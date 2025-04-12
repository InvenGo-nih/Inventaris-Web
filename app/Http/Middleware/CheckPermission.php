<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();

        // Ambil role ID user dari tabel users (pastikan ada kolom role_id di tabel users)
        $roleId = DB::table('users')->where('id', $user->id)->value('role_id');

        if (!$roleId) {
            return redirect()->back()->with('error', ['Anda tidak memiliki hak akses untuk halaman ini BAKAR']);
        }
        // Cek apakah role memiliki permission yang dibutuhkan
        $hasPermission = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $roleId)
            ->whereIn('permissions.name', (array) $permissions)
            ->exists();

        if (!$hasPermission) {
            return redirect()->back()->with('error', ['Anda tidak memiliki hak akses untuk halaman ini KONTOL']);
        }

        return $next($request);
    }
}
