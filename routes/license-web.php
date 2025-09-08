<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LicenseController;

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
Route::get('/license/activate', [LicenseController::class, 'showActivation'])
    ->name('license.activate')
    ->middleware(['auth']);

Route::post('/license/activate', [LicenseController::class, 'activate'])
    ->name('license.activate.store')
    ->middleware(['auth']);

// License status page
Route::get('/license/status', [LicenseController::class, 'status'])
    ->name('license.status')
    ->middleware(['auth']);

// License error page (for expired trials)
Route::get('/license/error', function () {
    return inertia('license/error', [
        'message' => session('error', 'License validation failed'),
        'trial_expired' => session('trial_expired', false),
    ]);
})->name('license.error');

// User access status API endpoint (for frontend components)
Route::get('/license/user-access-status', function () {
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not authenticated'
        ], 401);
    }

    return response()->json([
        'success' => true,
        'data' => $user->getAccessStatus() // @phpstan-ignore-line
    ]);
})->name('license.user-access-status')
  ->middleware(['auth']);
