<?php

use App\Models\Inventaris;
use App\Models\User;
use App\Services\SupabaseService;
use Database\Seeders\RolePermissionSeeder;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->mock(SupabaseService::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadFile')->andReturnTrue();
        $mock->shouldReceive('deleteFile')->andReturnTrue();
    });
});

it('requires a broken description when the condition is broken', function () {
    $user = User::factory()->create();
    $item = Inventaris::factory()->create(['condition' => 'Normal']);

    $this->actingAs($user)
        ->put(route('cek.update', $item->id), ['condition' => 'Rusak'])
        ->assertSessionHasErrors('broken_description');

    expect($item->fresh()->condition)->toBe('Normal');
});

it('updates the condition and broken description', function () {
    $user = User::factory()->create();
    $item = Inventaris::factory()->create(['condition' => 'Normal']);

    $this->actingAs($user)
        ->put(route('cek.update', $item->id), [
            'condition' => 'Rusak',
            'broken_description' => 'Layar retak',
        ])
        ->assertRedirect(route('cek.index'));

    $item->refresh();
    expect($item->condition)->toBe('Rusak')
        ->and($item->broken_description)->toBe('Layar retak');
});

it('clears the broken description when the condition is normal', function () {
    $user = User::factory()->create();
    $item = Inventaris::factory()->create([
        'condition' => 'Rusak',
        'broken_description' => 'Layar retak',
    ]);

    $this->actingAs($user)
        ->put(route('cek.update', $item->id), ['condition' => 'Normal'])
        ->assertRedirect(route('cek.index'));

    $item->refresh();
    expect($item->condition)->toBe('Normal')
        ->and($item->broken_description)->toBeNull();
});

it('forbids checking without the process permission', function () {
    $role = Role::where('name', 'teknisi')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id]);
    $item = Inventaris::factory()->create();

    $this->actingAs($user)
        ->put(route('cek.update', $item->id), ['condition' => 'Normal'])
        ->assertForbidden();
});
