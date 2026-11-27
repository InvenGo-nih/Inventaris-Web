<?php

use App\Models\Borrow;
use App\Models\Inventaris;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Cache::forget('dashboard:chart');
});

it('shows live inventory totals and chart data to users with dashboard access', function () {
    $role = Role::where('name', 'teknisi')->firstOrFail();
    $role->givePermissionTo('VIEW_DASHBOARD');
    $user = User::factory()->create(['role_id' => $role->id]);

    $normalItem = Inventaris::factory()->create([
        'quantity' => 4,
        'condition' => 'Normal',
    ]);
    $brokenItem = Inventaris::factory()->create([
        'quantity' => 2,
        'condition' => 'Rusak',
    ]);
    Inventaris::factory()->create([
        'condition' => 'Normal',
        'created_at' => now()->subMonth(),
    ]);

    Borrow::factory()->create(['inventaris_id' => $normalItem->id]);
    Borrow::factory()->returned()->create(['inventaris_id' => $brokenItem->id]);

    $this->actingAs($user)
        ->get(route('dashboard', ['month' => now()->month, 'year' => now()->year]))
        ->assertOk()
        ->assertSee('Kondisi Inventaris')
        ->assertSee('Barang Normal')
        ->assertSee('Barang Rusak')
        ->assertSee('Filter')
        ->assertSee('Peminjaman Aktif');

    $this->get(route('chart_data', ['month' => now()->month, 'year' => now()->year]))
        ->assertOk()
        ->assertJsonPath('data.InventarisCount', 3)
        ->assertJsonPath('data.BorrowCount', 1)
        ->assertJsonPath('data.NormalCount', 1)
        ->assertJsonPath('data.RusakCount', 1);
});
