<?php

use App\Http\Controllers\Api\AppUpdateClientController;
use App\Http\Controllers\UsbLockController;
use Illuminate\Support\Facades\Route;

// Main Application Entrypoint (enforces first-time install or hardware lockout)
Route::get('/', [UsbLockController::class, 'index'])->name('home');

// USB Hardware Lock Internal API Endpoints
Route::prefix('api/usb')->group(function () {
    Route::get('/status', [UsbLockController::class, 'status'])->name('usb.status');
    Route::post('/install', [UsbLockController::class, 'install'])->name('usb.install');
    Route::post('/reset', [UsbLockController::class, 'reset'])->name('usb.reset');
});

// Desktop Portable App 1-Click Update System
Route::prefix('api/app/update')->group(function () {
    Route::get('/check', [AppUpdateClientController::class, 'check'])->name('app.update.check');
    Route::post('/apply', [AppUpdateClientController::class, 'apply'])->name('app.update.apply');
});
