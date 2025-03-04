<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\pr9Auth;

Route::get('/', [StationController::class, 'test']);


Route::get('/serviceStart', [ServiceController::class, 'startService']);
Route::post('/dispatchCreate', [ServiceController::class, 'dispatchCreate']);
Route::post('/dispatchClear', [ServiceController::class, 'dispatchClear']);
Route::post('/dispatchDelete', [ServiceController::class, 'dispatchDelete']);
Route::post('/LineMessageCheck', [ServiceController::class, 'LineMessageCheck']);

Route::get('/auth', [StationController::class, 'Auth']);
Route::post('/authcheck', [StationController::class, 'AuthCheck']);
Route::post('/unauth', function () {  Auth::logout(); });

Route::get('/sms/{hn}', [PatientController::class, 'smsRequest']);
Route::post('/requestNumber', [PatientController::class, 'requestNumber']);

Route::get('/display/{station}', [StationController::class, 'displayPage']);
Route::post('/display/list', [StationController::class, 'displayList']);
Route::post('/display/updateCall', [StationController::class, 'updateCall']);

Route::middleware([pr9Auth::class])->group(function () {
    Route::get('/verify', [PatientController::class, 'verify']);
    Route::post('/verify', [PatientController::class, 'verifySearch']);
    
    Route::get('/history', [StationController::class, 'history']);
    
    Route::get('/station', [StationController::class, 'StationIndex']);
    Route::get('/station/{substation}', [StationController::class, 'Substation']);
    
    Route::get('/station/register/{id}', [StationController::class, 'Register']);
    Route::post('/station/register', [StationController::class, 'registerTask']);

    Route::get('/station/lab/count', [StationController::class, 'labCount']);

    Route::post('/station/call', [StationController::class, 'callTask']);
    Route::post('/station/callsound', [StationController::class, 'callTask']);
    Route::post('/station/hold', [StationController::class, 'holdTask']);
    Route::post('/station/success', [StationController::class, 'successTask']);
    Route::post('/station/delete', [StationController::class, 'deleteTask']);
    Route::post('/station/checksuccess', [StationController::class, 'checksuccessTask']);
    Route::post('/station/changeSSP', [StationController::class, 'changeSSPTask']);

    Route::post('/station/getTask', [StationController::class, 'getTask']);
    Route::post('/station/getSSP', [StationController::class, 'getSSP']);
    Route::post('/station/allTask', [StationController::class, 'allTask']);
    
});