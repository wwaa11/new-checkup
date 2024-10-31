<?php

use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StationController::class, 'test']);

Route::get('/station/index', [StationController::class, 'StationIndex']);
Route::get('/station/loginPage', [StationController::class, 'StationLoginPage']);
Route::post('/station/auth', [StationController::class, 'StationLoginAuth']);