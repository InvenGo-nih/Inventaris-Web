<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('redirects guests to login', function () {
    $this->get(route('inventaris.index'))->assertRedirect(route('login'));
});

it('forbids users without the required permission', function () {
    $role = Role::where('name', 'teknisi')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('inventaris.index'))
        ->assertForbidden();
});

it('allows users with the required permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('inventaris.index'))
        ->assertOk();
});

it('resolves permissions through the Spatie helper', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect(hasPermission('VIEW_INVENTARIS'))->toBeTrue()
        ->and(hasPermission('PERMISSION_THAT_DOES_NOT_EXIST'))->toBeFalse();
});
