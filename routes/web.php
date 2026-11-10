<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/parking-log', [DashboardController::class, 'parkingLog'])
        ->name('parking-log');

    Route::get('/parking-log/export', [DashboardController::class, 'exportParkingLog'])
        ->name('parking-log.export');

    Route::get('/parking-log/{log}', [DashboardController::class, 'show'])
        ->name('parking-log.show');
});

Route::get('/plate-captures/file/{path}', [DashboardController::class, 'plateFile'])
    ->where('path', '.*')
    ->name('plate-captures.file');

Route::get('/plate-captures/{sensor}', [DashboardController::class, 'plateCapture'])
    ->name('plate-captures.show');
