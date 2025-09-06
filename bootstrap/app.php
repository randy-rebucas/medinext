<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/license.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Removed global ApiAuth middleware - it should be applied per route group
        // $middleware->api(append: [
        //     \App\Http\Middleware\ApiAuth::class,
        // ]);

        $middleware->alias([
            'api.auth' => \App\Http\Middleware\ApiAuth::class,
            'api.clinic' => \App\Http\Middleware\ApiClinicAccess::class,
            'api.permission' => \App\Http\Middleware\ApiPermission::class,
            'license' => \App\Http\Middleware\LicenseValidation::class,
            'license.feature' => \App\Http\Middleware\LicenseFeatureValidation::class,
            'license.usage' => \App\Http\Middleware\LicenseUsageValidation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
