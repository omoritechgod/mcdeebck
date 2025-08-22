<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This is where users are redirected after login.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // Main API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Load auth routes at /api/register, /api/login, etc.
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/auth.php'));


            // Vendor-specific API routes at /api/vendor/*
            Route::middleware('api')
                ->prefix('api/vendor')
                ->group(base_path('routes/vendor.php'));

                        // ✅ Admin API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/admin.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
