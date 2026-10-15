<?php

use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\AdminPendriveController;
use App\Http\Controllers\Admin\AdminReleaseController;
use App\Http\Controllers\Api\LicenseVerificationController;
use App\Http\Controllers\Api\UpdateServerController;
use App\Http\Middleware\EnsureNotAppBuildMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Admin Portal & Central License Server Routes
|--------------------------------------------------------------------------
|
| These routes are STRICTLY for the central web server.
| They are completely separated from the desktop app routes and are NEVER
| packaged or included inside desktop application builds.
|
*/

// Central Server License Verification Endpoint (called by pendrives to verify hardware serial)
Route::post('/api/license/verify-usb', [LicenseVerificationController::class, 'verifyUsb'])->name('license.verify');

// Central Server App Update Endpoints (queried by portable desktop apps for 1-click upgrades)
Route::get('/api/updates/check', [UpdateServerController::class, 'check'])->name('updates.check');
Route::get('/api/updates/download/{version}', [UpdateServerController::class, 'download'])->name('updates.download');

// Central Web Admin Management Portal
Route::middleware([EnsureNotAppBuildMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminClientController::class, 'index'])->name('dashboard');
    Route::resource('clients', AdminClientController::class);

    // Client Pendrive Hardware Serial Registration & Management
    Route::post('clients/{client}/pendrives', [AdminPendriveController::class, 'store'])->name('clients.pendrives.store');
    Route::post('pendrives/{pendrive}/toggle', [AdminPendriveController::class, 'toggleStatus'])->name('pendrives.toggle');
    Route::delete('pendrives/{pendrive}', [AdminPendriveController::class, 'destroy'])->name('pendrives.destroy');

    // Fresh Portable Software Packaging & Downloads
    Route::get('packages', [AdminPackageController::class, 'index'])->name('packages.index');
    Route::post('packages/create-fresh', [AdminPackageController::class, 'createFresh'])->name('packages.create');
    Route::get('packages/{package}/download', [AdminPackageController::class, 'download'])->name('packages.download');
    Route::delete('packages/{package}', [AdminPackageController::class, 'destroy'])->name('packages.destroy');

    // Client Updates & Release Publishing
    Route::get('releases', [AdminReleaseController::class, 'index'])->name('releases.index');
    Route::post('releases', [AdminReleaseController::class, 'store'])->name('releases.store');
    Route::get('releases/{release}/download', [AdminReleaseController::class, 'download'])->name('releases.download');
    Route::post('releases/{release}/toggle', [AdminReleaseController::class, 'toggle'])->name('releases.toggle');
    Route::delete('releases/{release}', [AdminReleaseController::class, 'destroy'])->name('releases.destroy');
});
