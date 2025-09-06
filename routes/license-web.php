<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseWebController;

/*
|--------------------------------------------------------------------------
| License Web Routes
|--------------------------------------------------------------------------
|
| These routes handle license-related web pages including activation,
| error pages, and license management interfaces.
|
*/

// License activation page
Route::get('/license/activate', [LicenseWebController::class, 'showActivationForm'])
    ->name('license.activate.form');

Route::post('/license/activate', [LicenseWebController::class, 'activate'])
    ->name('license.activate');

// License error page
Route::get('/license/error', [LicenseWebController::class, 'error'])
    ->name('license.error');

// License status page
Route::get('/license/status', [LicenseWebController::class, 'status'])
    ->name('license.status')
    ->middleware(['auth']);

// License management (admin only)
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/license/manage', [LicenseWebController::class, 'manage'])
        ->name('license.manage');
    
    Route::get('/license/usage', [LicenseWebController::class, 'usage'])
        ->name('license.usage');
});
