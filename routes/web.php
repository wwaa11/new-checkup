<?php

use App\Http\Controllers\ObsController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use App\Http\Middleware\obsAuth;
use App\Http\Middleware\pr9Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [ServiceController::class, 'test']);

// Services
Route::get('/serviceStart', [ServiceController::class, 'startService']);
Route::post('/dispatchCreate', [ServiceController::class, 'dispatchCreate']);
Route::post('/dispatchClear', [ServiceController::class, 'dispatchClear']);
Route::post('/dispatchDelete', [ServiceController::class, 'dispatchDelete']);
Route::post('/LineMessageCheck', [ServiceController::class, 'LineMessageCheck']);
Route::post('/restart-service', [ServiceController::class, 'restartService']);

Route::get('/station/displaylist', [StationController::class, 'displayPageJson']);

// Auth
Route::get('/auth', [StationController::class, 'Auth']);
Route::post('/authcheck', [StationController::class, 'AuthCheck']);
Route::post('/unauth', function () {Auth::logout();});
// Send SMS
Route::get('/sms/{hn}', [PatientController::class, 'smsRequest']);
Route::post('/requestNumber', [PatientController::class, 'requestNumber']);
// Display
Route::get('/display/{station}', [StationController::class, 'displayPage']);
Route::post('/display/list', [StationController::class, 'displayList'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
Route::post('/display/updateCall', [StationController::class, 'updateCall'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// VitalSign Lab
Route::middleware([pr9Auth::class])->group(function () {
    // History
    Route::get('/history', [StationController::class, 'history']);
    // Generate Number
    Route::get('/verify', [PatientController::class, 'verify']);
    Route::post('/verify', [PatientController::class, 'verifySearch']);
    // Station Vitalsign, Lab
    Route::get('/station', [StationController::class, 'StationIndex']);
    Route::get('/station/{substation}', [StationController::class, 'Substation']);
    Route::get('/station/register/{id}', [StationController::class, 'Register']);
    Route::post('/station/register', [StationController::class, 'registerTask']);
    Route::post('/station/call', [StationController::class, 'callTask']);
    Route::post('/station/callsound', [StationController::class, 'callTask']);
    Route::post('/station/hold', [StationController::class, 'holdTask']);
    Route::post('/station/success', [StationController::class, 'successTask']);
    Route::post('/station/delete', [StationController::class, 'deleteTask']);
    Route::post('/station/checksuccess', [StationController::class, 'checksuccessTask']);
    Route::post('/station/getTask', [StationController::class, 'getTask']);
    Route::post('/station/getSSP', [StationController::class, 'getSSP']);
    Route::post('/station/allTask', [StationController::class, 'allTask']);
    // Lab order
    Route::get('/station/lab/count', [StationController::class, 'labCount']);
    Route::post('/station/changeSSP', [StationController::class, 'changeSSPTask']);

});

// OBS
Route::get('/obs/auth', [ObsController::class, 'auth'])->name('obs.auth');
Route::middleware([obsAuth::class])->group(function () {
    Route::get('/obs', [ObsController::class, 'index'])->name('obs.index');
    Route::get('/obs/registeration', [ObsController::class, 'registeration'])->name('obs.registeration');
    Route::get('/obs/substation/{substation_id}', [ObsController::class, 'substation'])->name('obs.substation');
    Route::post('/obs/registeration/update-doctor', [ObsController::class, 'updateDoctor'])->name('obs.registeration.update-doctor');
    Route::post('/obs/registeration/remove-doctor', [ObsController::class, 'removeDoctor'])->name('obs.registeration.remove-doctor');
    Route::post('/obs/registeration/register-patient', [ObsController::class, 'registerPatient'])->name('obs.registeration.register-patient');
    Route::post('/obs/substation/getTask', [ObsController::class, 'getTask'])->name('obs.substation.getTask');
    Route::post('/obs/substation/skipPatient', [ObsController::class, 'skipPatient'])->name('obs.substation.skipPatient');
    Route::post('/obs/substation/callPatient', [ObsController::class, 'callPatient'])->name('obs.substation.callPatient');
    Route::post('/obs/substation/callAgainPatient', [ObsController::class, 'callAgainPatient'])->name('obs.substation.callAgainPatient');
    Route::post('/obs/substation/cancelPatient', [ObsController::class, 'cancelPatient'])->name('obs.substation.cancelPatient');
    Route::post('/obs/substation/successPatient', [ObsController::class, 'successPatient'])->name('obs.substation.successPatient');
});
// Display
Route::get('/obs/display', [ObsController::class, 'display'])->name('obs.display');
Route::post('/obs/display/list', [ObsController::class, 'displayList'])->name('obs.display.list');
Route::post('/obs/display/updateCall', [ObsController::class, 'displayUpdateCall'])->name('obs.display.updateCall');
