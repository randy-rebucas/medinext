<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenseController;

/*
|--------------------------------------------------------------------------
| License API Routes
|--------------------------------------------------------------------------
|
| These routes handle all license-related API endpoints including
| activation, validation, usage tracking, and administrative functions.
|
*/

Route::prefix('v1/license')->group(function () {

    // Public license routes (no authentication required for activation)
    Route::post('/activate', [LicenseController::class, 'activate'])
        ->name('api.license.activate');

    Route::post('/validate', [LicenseController::class, 'validate'])
        ->name('license.validate');

    // User license activation (requires authentication)
    Route::middleware(['api.auth'])->group(function () {
        Route::post('/activate-user', [LicenseController::class, 'activateForUser'])
            ->name('api.license.activate-user');
    });

    // Protected license routes (authentication required)
    Route::middleware(['api.auth'])->group(function () {

        // License status and information
        Route::get('/status', [LicenseController::class, 'status'])
            ->name('api.license.status');

        Route::get('/user-access-status', [LicenseController::class, 'userAccessStatus'])
            ->name('api.license.user-access-status');

        Route::get('/info', [LicenseController::class, 'info'])
            ->name('license.info');

        // Feature management
        Route::get('/feature/{feature}', [LicenseController::class, 'hasFeature'])
            ->name('license.feature')
            ->where('feature', '[a-zA-Z_]+');

        // Usage management
        Route::prefix('usage')->group(function () {
            Route::get('/check', [LicenseController::class, 'checkUsage'])
                ->name('license.usage.check');

            Route::get('/', [LicenseController::class, 'usage'])
                ->name('api.license.usage');

            Route::post('/increment', [LicenseController::class, 'incrementUsage'])
                ->name('license.usage.increment');

            Route::post('/decrement', [LicenseController::class, 'decrementUsage'])
                ->name('license.usage.decrement');

            Route::post('/reset-monthly', [LicenseController::class, 'resetMonthlyUsage'])
                ->name('license.usage.reset-monthly');
        });

        // Administrative routes (admin only)
        Route::middleware(['api.permission:admin'])->group(function () {
            Route::get('/statistics', [LicenseController::class, 'statistics'])
                ->name('license.statistics');

            Route::get('/expiring', [LicenseController::class, 'expiring'])
                ->name('license.expiring');
        });
    });
});
