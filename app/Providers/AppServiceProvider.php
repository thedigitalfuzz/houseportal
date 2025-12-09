<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthAnyGuard;
use App\Providers\StaffUserProvider;


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
    public function boot()
    {
        Blade::component('components.sidebar-link', 'sidebar-link');
        Route::aliasMiddleware('auth.any', AuthAnyGuard::class);
        Auth::provider('staffs', function ($app, array $config) {
            return new \App\Providers\StaffUserProvider();
        });
    }
}
