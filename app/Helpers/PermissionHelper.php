<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (!function_exists('hasPermission')) {
    function hasPermission($permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $roleId = DB::table('users')->where('id', $user->id)->value('role_id');

        if (!$roleId) {
            return false;
        }

        return DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $roleId)
            ->whereIn('permissions.name', (array) $permissions)
            ->exists();
    }
}
