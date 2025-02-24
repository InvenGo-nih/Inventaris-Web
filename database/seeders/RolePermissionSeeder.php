<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data lama agar tidak duplikat
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        // Aktifkan kembali foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Buat Role
        Role::updateOrCreate(['name' => 'admin']);
        Role::updateOrCreate(['name' => 'teknisi']);
        Role::updateOrCreate(['name' => 'guru']);

        // Tambahkan pengguna baru
        DB::table('users')->updateOrInsert(
            ['email' => 'rifkiadi348@gmail.com'], // Kriteria pencarian
            [
                'name' => 'rifki',
                'password' => bcrypt('11111111'), // Enkripsi password
                'role_id' => 1, // ID untuk role 'admin'
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'zami123@gmail.com'], // Kriteria pencarian
            [
                'name' => 'zami',
                'password' => bcrypt('11111111'), // Enkripsi password
                'role_id' => 1, // ID untuk role 'admin'
            ]
        );

        // Buat Permissions CRUD Inventaris
        $permissions = [
            ['name' => 'VIEW_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'CREATE_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'EDIT_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'SHOW_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'DELETE_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'VIEW_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'CREATE_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'EDIT_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'DELETE_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'VIEW_SCAN', 'permission_group' => 'scan'],
            ['name' => 'SCAN_PROCESS', 'permission_group' => 'scan'],
            ['name' => 'VIEW_PROFILE', 'permission_group' => 'profile'],
            ['name' => 'EDIT_PROFILE', 'permission_group' => 'profile'],
            ['name' => 'DELETE_PROFILE', 'permission_group' => 'profile'],
            ['name' => 'VIEW_USERS', 'permission_group' => 'user'],
            ['name' => 'CREATE_USERS', 'permission_group' => 'user'],
            ['name' => 'EDIT_USERS', 'permission_group' => 'user'],
            ['name' => 'DELETE_USERS', 'permission_group' => 'user'],
            ['name' => 'VIEW_ROLES', 'permission_group' => 'role'],
            ['name' => 'CREATE_ROLES', 'permission_group' => 'role'],
            ['name' => 'EDIT_ROLES', 'permission_group' => 'role'],
            ['name' => 'DELETE_ROLES', 'permission_group' => 'role'],
            ['name' => 'VIEW_DASHBOARD', 'permission_group' => 'dashboard'],
            ['name' => 'CHART_DATA', 'permission_group' => 'dashboard'],
        ];
    
            foreach ($permissions as $perm) {
                Permission::updateOrCreate(['name' => $perm['name']], ['permission_group' => $perm['permission_group']]);
            }

        // Tambahkan seeder untuk role_has_permissions
        $roleId = 1; // ID untuk role 'admin'
        $permissions = DB::table('permissions')->pluck('id'); // Ambil semua permission_id

        foreach ($permissions as $permissionId) {
            DB::table('role_has_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }
}
