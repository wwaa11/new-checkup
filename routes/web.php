<?php

use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\pr9Auth;

Route::get('/', [StationController::class, 'test']);

Route::get('/auth', [StationController::class, 'Auth']);
Route::post('/authcheck', [StationController::class, 'AuthCheck']);
Route::post('/unauth', function () {  Auth::logout(); });

Route::middleware([pr9Auth::class])->group(function () {
    Route::get('/station', [StationController::class, 'StationIndex']);
    
});