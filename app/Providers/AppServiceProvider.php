<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $path = sys_get_temp_dir() . '/views';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        View::addLocation($path);

        if (env('APP_PRODUCTION') === 'PRODUCTION') {
            URL::forceScheme('https');
        }

        Blade::if('hasPermission', function ($permissions) {
            if (!Auth::check()) {
                return false;
            }
    
            $user = Auth::user();
    
            // Ambil role ID user dari tabel users (pastikan ada kolom role_id di tabel users)
            $roleId = DB::table('users')->where('id', $user->id)->value('role_id');

            if (!$roleId) {
                return redirect()->back()->with('error', 'You do not have permission to access this page.');
            }

            // Cek apakah role memiliki salah satu dari permission yang dibutuhkan
            return DB::table('role_has_permissions')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('role_has_permissions.role_id', $roleId)
                ->whereIn('permissions.name', (array) $permissions) // Menggunakan whereIn untuk memeriksa beberapa permission
                ->exists();
        });
    }
}
