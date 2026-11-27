<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a role with permissions', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'operator',
            'permissions' => ['VIEW_INVENTARIS'],
        ])
        ->assertRedirect(route('roles.index'));

    $role = Role::where('name', 'operator')->firstOrFail();

    expect($role->hasPermissionTo('VIEW_INVENTARIS'))->toBeTrue()
        ->and($role->permissions()->count())->toBe(1);
});

it('replaces permissions when updating a role', function () {
    $admin = User::factory()->create();
    $role = Role::create(['name' => 'operator', 'guard_name' => 'web']);
    $role->syncPermissions(['VIEW_INVENTARIS']);

    $this->actingAs($admin)
        ->put(route('roles.update', $role), [
            'name' => 'operator',
            'permissions' => ['VIEW_BORROW'],
        ])
        ->assertRedirect(route('roles.index'));

    $role->refresh();

    expect($role->hasPermissionTo('VIEW_BORROW'))->toBeTrue()
        ->and($role->hasPermissionTo('VIEW_INVENTARIS'))->toBeFalse();
});

it('does not delete a role that is still assigned', function () {
    $admin = User::factory()->create();
    $role = Role::create(['name' => 'operator', 'guard_name' => 'web']);
    User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $role))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});
