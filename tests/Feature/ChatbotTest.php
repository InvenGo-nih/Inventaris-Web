<?php

use App\Models\User;
use App\Services\GeminiChatService;
use App\Services\InventoryContextService;
use Database\Seeders\RolePermissionSeeder;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function chatbotAdminUser(): User
{
    $role = Role::where('name', 'admin')->firstOrFail();

    return User::factory()->create(['role_id' => $role->id]);
}

it('forbids users without VIEW_CHATBOT', function () {
    $role = Role::where('name', 'teknisi')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('chatbot.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->getJson(route('chatbot.usage'))
        ->assertForbidden();
});

it('renders the chatbot page for permitted users', function () {
    $this->actingAs(chatbotAdminUser())
        ->get(route('chatbot.index'))
        ->assertOk()
        ->assertSee('Chatbot InvenGo')
        ->assertSee('bukan kuota resmi Google AI Studio')
        ->assertSee('https://aistudio.google.com/rate-limit', false);
});

it('validates the chat message', function () {
    $user = chatbotAdminUser();

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), ['message' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), ['message' => str_repeat('a', 1001)])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

it('does not expose sensitive user attributes in the context', function () {
    $context = app(InventoryContextService::class)->build(chatbotAdminUser());
    $json = json_encode($context);

    expect($json)->not->toContain('password')
        ->and($json)->not->toContain('remember_token')
        ->and($json)->not->toContain('api_key');
});

it('omits user data for users without VIEW_USERS', function () {
    $role = Role::where('name', 'teknisi')->firstOrFail();
    $user = User::factory()->create([
        'role_id' => $role->id,
        'email' => 'rahasia-chatbot@example.test',
    ]);

    $context = app(InventoryContextService::class)->build($user);

    expect($context)->not->toHaveKey('users')
        ->and($context)->not->toHaveKey('peminjaman')
        ->and(json_encode($context))->not->toContain('rahasia-chatbot@example.test');
});

it('refuses off-topic questions with the exact message', function () {
    $this->mock(GeminiChatService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('ask');
    });

    $this->actingAs(chatbotAdminUser())
        ->postJson(route('chatbot.ask'), ['message' => 'Cuaca di Jakarta gimana?'])
        ->assertOk()
        ->assertJson([
            'status' => 200,
            'success' => true,
            'data' => ['reply' => 'Maaf, saya hanya menjawab pertanyaan tentang website ini saja.'],
        ])
        ->assertJsonPath('data.usage.request_hari_ini', 0);
});

it('monitors chatbot requests and limits', function () {
    config([
        'services.gemini.daily_limit' => 200,
        'services.gemini.minute_limit' => 10,
    ]);

    $this->mock(GeminiChatService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ask')->once()->andReturn('Ada 2 barang rusak.');
    });

    $user = chatbotAdminUser();

    $this->actingAs($user)
        ->getJson(route('chatbot.usage'))
        ->assertOk()
        ->assertJsonPath('data.limit_harian', 200)
        ->assertJsonPath('data.limit_per_menit', 10)
        ->assertJsonPath('data.request_hari_ini', 0)
        ->assertJsonPath('data.request_total', 0)
        ->assertJsonPath('data.sisa_hari_ini', 200)
        ->assertJsonPath('data.sumber', 'Aplikasi InvenGo (estimasi)')
        ->assertJsonPath('data.zona_reset', 'America/Los_Angeles');

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), ['message' => 'Berapa barang rusak?'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.usage.request_hari_ini', 1)
        ->assertJsonPath('data.usage.request_total', 1)
        ->assertJsonPath('data.usage.request_menit_ini', 1)
        ->assertJsonPath('data.usage.sisa_hari_ini', 199)
        ->assertJsonPath('data.usage.global_hari_ini', 1)
        ->assertJsonPath('data.usage.global_menit_ini', 1)
        ->assertJsonPath('data.usage.global_total', 1)
        ->assertJsonPath('data.usage.sisa_global_hari_ini', 199)
        ->assertJsonPath('data.usage.sisa_global_per_menit', 9);

    $this->actingAs($user)
        ->getJson(route('chatbot.usage'))
        ->assertOk()
        ->assertJsonPath('data.request_hari_ini', 1)
        ->assertJsonPath('data.request_total', 1)
        ->assertJsonPath('data.request_menit_ini', 1)
        ->assertJsonPath('data.global_hari_ini', 1)
        ->assertJsonPath('data.global_menit_ini', 1)
        ->assertJsonPath('data.global_total', 1);
});

it('shows app-wide chatbot usage to other permitted users', function () {
    config([
        'services.gemini.daily_limit' => 200,
        'services.gemini.minute_limit' => 10,
    ]);

    $this->mock(GeminiChatService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ask')->once()->andReturn('Ada 2 barang rusak.');
    });

    $firstUser = chatbotAdminUser();
    $secondUser = chatbotAdminUser();

    $this->actingAs($firstUser)
        ->postJson(route('chatbot.ask'), ['message' => 'Berapa barang rusak?'])
        ->assertOk()
        ->assertJsonPath('data.usage.request_hari_ini', 1)
        ->assertJsonPath('data.usage.global_hari_ini', 1);

    $this->actingAs($secondUser)
        ->getJson(route('chatbot.usage'))
        ->assertOk()
        ->assertJsonPath('data.request_hari_ini', 0)
        ->assertJsonPath('data.global_hari_ini', 1);
});

it('resets the daily counter at midnight Pacific time', function () {
    config([
        'services.gemini.daily_limit' => 200,
        'services.gemini.minute_limit' => 10,
    ]);

    $this->mock(GeminiChatService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ask')->once()->andReturn('Ada 2 barang rusak.');
    });

    $user = chatbotAdminUser();

    $this->travelTo(\Carbon\Carbon::parse('2026-11-27 07:59:00', 'UTC'));

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), ['message' => 'Berapa barang rusak?'])
        ->assertOk()
        ->assertJsonPath('data.usage.request_hari_ini', 1);

    $this->travelTo(\Carbon\Carbon::parse('2026-11-27 08:01:00', 'UTC'));

    $this->actingAs($user)
        ->getJson(route('chatbot.usage'))
        ->assertOk()
        ->assertJsonPath('data.request_hari_ini', 0)
        ->assertJsonPath('data.global_hari_ini', 0);
});

it('returns a friendly response when the chatbot is disabled', function () {
    config(['services.gemini.enabled' => false]);

    $this->actingAs(chatbotAdminUser())
        ->postJson(route('chatbot.ask'), ['message' => 'Berapa barang rusak?'])
        ->assertStatus(503)
        ->assertJson([
            'status' => 'error',
            'success' => false,
            'data' => ['reply' => 'Chatbot sedang dinonaktifkan.'],
        ]);
});

it('returns the gemini answer in data.reply', function () {
    $this->mock(GeminiChatService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ask')
            ->once()
            ->withArgs(fn ($systemPrompt, $question) => is_string($systemPrompt)
                && str_contains($systemPrompt, 'Konteks data website')
                && $question === 'Berapa barang rusak?')
            ->andReturn('Saat ini ada 3 barang rusak.');
    });

    $this->actingAs(chatbotAdminUser())
        ->postJson(route('chatbot.ask'), ['message' => 'Berapa barang rusak?'])
        ->assertOk()
        ->assertJson([
            'status' => 200,
            'success' => true,
            'data' => ['reply' => 'Saat ini ada 3 barang rusak.'],
        ]);
});
