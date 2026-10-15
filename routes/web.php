<?php

use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\Admin\AdminPendriveController;
use App\Http\Controllers\Api\LicenseVerificationController;
use App\Http\Controllers\UsbLockController;
use App\Http\Middleware\EnsureNotAppBuildMiddleware;
use Illuminate\Support\Facades\Route;

// Main Application Entrypoint (enforces first-time install or hardware lockout)
Route::get('/', [UsbLockController::class, 'index'])->name('home');

// USB Hardware Lock Internal API Endpoints
Route::prefix('api/usb')->group(function () {
    Route::get('/status', [UsbLockController::class, 'status'])->name('usb.status');
    Route::post('/install', [UsbLockController::class, 'install'])->name('usb.install');
    Route::post('/reset', [UsbLockController::class, 'reset'])->name('usb.reset');
});

// Central Server License Verification Endpoint (only available on central web server)
if (class_exists(LicenseVerificationController::class)) {
    Route::post('/api/license/verify-usb', [LicenseVerificationController::class, 'verifyUsb'])->name('license.verify');
}

// Central Web Admin Management Portal (STRICTLY Web Server only - NEVER built into desktop app)
if (class_exists(AdminClientController::class)) {
    Route::middleware([EnsureNotAppBuildMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminClientController::class, 'index'])->name('dashboard');
        Route::resource('clients', AdminClientController::class);

        // Client Pendrive Hardware Serial Registration & Management
        Route::post('clients/{client}/pendrives', [AdminPendriveController::class, 'store'])->name('clients.pendrives.store');
        Route::post('pendrives/{pendrive}/toggle', [AdminPendriveController::class, 'toggleStatus'])->name('pendrives.toggle');
        Route::delete('pendrives/{pendrive}', [AdminPendriveController::class, 'destroy'])->name('pendrives.destroy');
    });
}
