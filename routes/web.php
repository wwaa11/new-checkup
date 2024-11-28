<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\pr9Auth;

Route::get('/', [StationController::class, 'test'])->name('test');

Route::get('/serviceStart', [ServiceController::class, 'startService']);

Route::get('/auth', [StationController::class, 'Auth']);
Route::post('/authcheck', [StationController::class, 'AuthCheck']);
Route::post('/unauth', function () {  Auth::logout(); });

Route::get('/sms/{hn}', [PatientController::class, 'smsRequest']);
Route::post('/requestNumber', [PatientController::class, 'requestNumber']);

Route::middleware([pr9Auth::class])->group(function () {
    Route::get('/verify', [PatientController::class, 'verify']);
    Route::post('/verify', [PatientController::class, 'verifySearch']);
    
    Route::get('/history', [StationController::class, 'history']);
    
    Route::get('/station', [StationController::class, 'StationIndex']);
    Route::get('/station/{substation}', [StationController::class, 'Substation']);
    
    Route::get('/station/register/{id}', [StationController::class, 'Register']);
    Route::post('/station/register', [StationController::class, 'registerTask']);

    Route::post('/station/call', [StationController::class, 'callTask']);
    Route::post('/station/hold', [StationController::class, 'holdTask']);
    Route::post('/station/success', [StationController::class, 'successTask']);
    Route::post('/station/delete', [StationController::class, 'deleteTask']);
    Route::post('/station/checksuccess', [StationController::class, 'checksuccessTask']);

    Route::post('/station/getTask', [StationController::class, 'getTask']);
    Route::post('/station/allTask', [StationController::class, 'allTask']);
    
});