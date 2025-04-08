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
        // Nonaktifkan foreign key checks (HIDUPKAN JIKA MENGGUNAKAN DATABASE LOKAL)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data lama agar tidak duplikat
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        // Aktifkan kembali foreign key checks (HIDUPKAN JIKA MENGGUNAKAN DATABASE LOKAL)
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Buat Role
        Role::updateOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'teknisi'], ['guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'guru'], ['guard_name' => 'web']);

        // Tambahkan pengguna baru
        DB::table('users')->updateOrInsert(
            ['email' => 'rifkiadi348@gmail.com'], // Kriteria pencarian
            [
                'name' => 'rifki',
                'password' => bcrypt('11111111'), // Bcrypt password
                'role_id' => 1, // ID untuk role 'admin'
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'zami123@gmail.com'], // Kriteria pencarian
            [
                'name' => 'zami',
                'password' => bcrypt('11111111'), // Bcrypt password
                'role_id' => 1, // ID untuk role 'admin'
            ]
        );

        // Buat Permissions CRUD Inventaris
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
        ];
    
            foreach ($permissions as $perm) {
                Permission::updateOrCreate(['name' => $perm['name']], ['permission_group' => $perm['permission_group'], 'alias' => $perm['alias']]);
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
