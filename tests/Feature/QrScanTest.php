<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('rejects QR data from another host', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('qr.process'), ['qr_data' => 'https://evil.example/qr/1'])
        ->assertSessionHasErrors('qr_data');
});

it('redirects to QR data from the application host', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('qr.process'), ['qr_data' => config('app.url').'/qr/1'])
        ->assertRedirect(config('app.url').'/qr/1');
});

it('forbids scanning without the process permission', function () {
    $role = Role::where('name', 'teknisi')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->post(route('qr.process'), ['qr_data' => config('app.url').'/qr/1'])
        ->assertForbidden();
});
