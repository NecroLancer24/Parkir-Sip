<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/parking-log/{log}/plate-capture', [DashboardController::class, 'storePlateCapture'])
    ->name('api.parking-log.plate-capture');
