<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $roles = collect(['admin', 'teknisi', 'guru'])
            ->mapWithKeys(fn ($name) => [
                $name => Role::updateOrCreate(
                    ['name' => $name, 'guard_name' => $guard],
                    ['guard_name' => $guard]
                ),
            ]);

        $permissions = [
            ['name' => 'VIEW_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Lihat Inventaris'],
            ['name' => 'CREATE_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Buat Inventaris'],
            ['name' => 'EDIT_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Edit Inventaris'],
            ['name' => 'SHOW_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Detail Inventaris'],
            ['name' => 'DELETE_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Hapus Inventaris'],
            ['name' => 'PDF_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'PDF Inventaris'],
            ['name' => 'VIEW_BORROW', 'permission_group' => 'peminjaman', 'alias' => 'Lihat Peminjaman'],
            ['name' => 'CREATE_BORROW', 'permission_group' => 'peminjaman', 'alias' => 'Buat Peminjaman'],
            ['name' => 'EDIT_BORROW', 'permission_group' => 'peminjaman', 'alias' => 'Edit Peminjaman'],
            ['name' => 'DELETE_BORROW', 'permission_group' => 'peminjaman', 'alias' => 'Hapus Peminjaman'],
            ['name' => 'VIEW_SCAN', 'permission_group' => 'scan', 'alias' => 'Lihat Scan'],
            ['name' => 'SCAN_PROCESS', 'permission_group' => 'scan', 'alias' => 'Proses Scan'],
            ['name' => 'VIEW_USERS', 'permission_group' => 'user', 'alias' => 'Lihat Pengguna'],
            ['name' => 'CREATE_USERS', 'permission_group' => 'user', 'alias' => 'Buat Pengguna'],
            ['name' => 'EDIT_USERS', 'permission_group' => 'user', 'alias' => 'Edit Pengguna'],
            ['name' => 'DELETE_USERS', 'permission_group' => 'user', 'alias' => 'Hapus Pengguna'],
            ['name' => 'VIEW_ROLES', 'permission_group' => 'role', 'alias' => 'Lihat Jabatan'],
            ['name' => 'CREATE_ROLES', 'permission_group' => 'role', 'alias' => 'Buat Jabatan'],
            ['name' => 'EDIT_ROLES', 'permission_group' => 'role', 'alias' => 'Edit Jabatan'],
            ['name' => 'DELETE_ROLES', 'permission_group' => 'role', 'alias' => 'Hapus Jabatan'],
            ['name' => 'VIEW_DASHBOARD', 'permission_group' => 'dashboard', 'alias' => 'Lihat Beranda'],
            ['name' => 'CHART_DATA', 'permission_group' => 'dashboard', 'alias' => 'Data Grafik'],
            ['name' => 'VIEW_LOCATION_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Lihat Lokasi Inventaris'],
            ['name' => 'CREATE_LOCATION_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Buat Lokasi Inventaris'],
            ['name' => 'EDIT_LOCATION_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Edit Lokasi Inventaris'],
            ['name' => 'DELETE_LOCATION_INVENTARIS', 'permission_group' => 'inventaris', 'alias' => 'Hapus Lokasi Inventaris'],
            ['name' => 'VIEW_CEK', 'permission_group' => 'cek', 'alias' => 'Lihat Pengecekan'],
            ['name' => 'PROCESS_CEK', 'permission_group' => 'cek', 'alias' => 'Proses Pengecekan'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => $guard],
                [
                    'permission_group' => $permission['permission_group'],
                    'alias' => $permission['alias'],
                ]
            );
        }

        $roles['admin']->syncPermissions(collect($permissions)->pluck('name')->all());

        User::query()
            ->whereNotNull('role_id')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($roles) {
                foreach ($users as $user) {
                    $role = $roles->first(fn (Role $role) => $role->id === (int) $user->role_id);

                    if ($role) {
                        $user->syncRoles([$role]);
                    }
                }
            });

        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('ADMIN_NAME', 'Administrator'),
                    'password' => Hash::make($adminPassword),
                    'role_id' => $roles['admin']->id,
                ]
            );

            $admin->syncRoles([$roles['admin']]);
        }
    }
}
