<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        $path = sys_get_temp_dir().'/views';
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }
        View::addLocation($path);

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Blade::if('hasPermission', function ($permissions) {
            return hasPermission($permissions);
        });
    }
}
