<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/parking-log', [DashboardController::class, 'parkingLog'])
    ->name('parking-log');

Route::get('/parking-log/{log}', [DashboardController::class, 'show'])
    ->name('parking-log.show');

Route::get('/plate-captures/file/{path}', [DashboardController::class, 'plateFile'])
    ->where('path', '.*')
    ->name('plate-captures.file');

Route::get('/plate-captures/{sensor}', [DashboardController::class, 'plateCapture'])
    ->name('plate-captures.show');
