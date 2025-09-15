<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallationController;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes handle the installation process for MediNext EMR.
| They are only accessible when the system is not yet installed.
|
*/

Route::prefix('install')->name('installation.')->group(function () {
    // Installation welcome page
    Route::get('/', [InstallationController::class, 'index'])->name('index');
    
    // Database configuration
    Route::get('/database', [InstallationController::class, 'database'])->name('database');
    Route::post('/database', [InstallationController::class, 'configureDatabase'])->name('database.configure');
    
    // Admin user creation
    Route::get('/admin', [InstallationController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallationController::class, 'createAdmin'])->name('admin.create');
    
    // Installation complete
    Route::get('/complete', [InstallationController::class, 'complete'])->name('complete');
});
