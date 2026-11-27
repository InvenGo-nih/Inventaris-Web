<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        Permission::updateOrCreate(
            ['name' => 'VIEW_CHATBOT', 'guard_name' => $guard],
            ['permission_group' => 'chatbot', 'alias' => 'Lihat Chatbot']
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'teknisi', 'guru'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->first();

            if ($role && ! $role->hasPermissionTo('VIEW_CHATBOT')) {
                $role->givePermissionTo('VIEW_CHATBOT');
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        Permission::query()
            ->where('name', 'VIEW_CHATBOT')
            ->where('guard_name', $guard)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
