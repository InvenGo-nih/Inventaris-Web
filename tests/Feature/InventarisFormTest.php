<?php

use App\Models\GroupInventaris;
use App\Models\Inventaris;
use App\Models\User;
use App\Services\SupabaseService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->mock(SupabaseService::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadFile')->andReturnTrue();
        $mock->shouldReceive('deleteFile')->andReturnTrue();
    });
});

it('stores an inventory item in the requested group', function () {
    $user = User::factory()->create();
    $group = GroupInventaris::factory()->create();

    $this->actingAs($user)
        ->post(route('inventaris.store'), [
            'group_inventaris_id' => $group->id,
            'name' => 'Laptop Test',
            'serial_number' => 'SN-TEST-001',
            'condition' => 'Normal',
            'specification' => 'Core i5',
            'location' => 'Ruang IT',
            'image' => UploadedFile::fake()->image('inventaris.jpg'),
        ])
        ->assertRedirect(route('inventaris.inventaris', $group->id));

    $item = Inventaris::firstOrFail();

    expect($item->group_inventaris_id)->toBe($group->id)
        ->and($item->qr_link)->toContain('/qr/'.$item->id);
});

it('renders the inventory form with the group id', function () {
    $user = User::factory()->create();
    $group = GroupInventaris::factory()->create();

    $this->actingAs($user)
        ->get(route('inventaris.form', ['group_inventaris_id' => $group->id]))
        ->assertOk()
        ->assertSee('name="group_inventaris_id"', false)
        ->assertSee('value="'.$group->id.'"', false);
});

it('requires authentication and permission to show a QR page', function () {
    $item = Inventaris::factory()->create();

    $this->get(route('test_qr', $item->id))->assertRedirect(route('login'));

    $role = Role::where('name', 'teknisi')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('test_qr', $item->id))
        ->assertForbidden();
});
