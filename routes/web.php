<?php

use App\Http\Controllers\UsbLockController;
use Illuminate\Support\Facades\Route;

// Main Web Application Entrypoint (enforces first-time install or hardware lockout)
Route::get('/', [UsbLockController::class, 'index'])->name('home');

// USB Hardware Lock API Endpoints
Route::prefix('api/usb')->group(function () {
    Route::get('/status', [UsbLockController::class, 'status'])->name('usb.status');
    Route::post('/install', [UsbLockController::class, 'install'])->name('usb.install');
    Route::post('/reset', [UsbLockController::class, 'reset'])->name('usb.reset');
});
