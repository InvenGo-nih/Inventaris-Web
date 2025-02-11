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

        // Buat Permissions CRUD Inventaris
        $permissions = [
            ['name' => 'VIEW_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'CREATE_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'READ_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'UPDATE_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'DELETE_INVENTARIS', 'permission_group' => 'inventaris'],
            ['name' => 'CREATE_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'READ_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'UPDATE_BORROW', 'permission_group' => 'peminjaman'],
            ['name' => 'DELETE_BORROW', 'permission_group' => 'peminjaman']];
    
            foreach ($permissions as $perm) {
                Permission::updateOrCreate(['name' => $perm['name']], ['permission_group' => $perm['permission_group']]);
            }
    }
}
