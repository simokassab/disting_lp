<?php

use App\Http\Controllers\HeController;
use App\Http\Controllers\PinController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Standard Subscription Routes
|--------------------------------------------------------------------------
*/
Route::controller(HeController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/verify', 'verify');
    Route::get('/success', 'success');
    Route::get('/failure', 'failure');
    Route::get('/get-request-headers', 'getRequestHeaders');

    Route::post('/get-antifraud-script', 'getAntiFraudScript');
    Route::post('/save-preferred-language', 'savePreferredLanguage');
    Route::post('/handle-subscription', 'handleSubscription');
    Route::post('/store-tracking', 'storeTracking');
});

/*
|--------------------------------------------------------------------------
| PIN Verification Routes
|--------------------------------------------------------------------------
*/
Route::get('/pin', [PinController::class, 'pin']);
Route::get('/otp', [PinController::class, 'otpVerification']);
Route::post('/pin-store-tracking', [PinController::class, 'storeTracking']);
Route::post('/pin-get-antifraud-script', [PinController::class, 'getAntiFraudScript']);
Route::post('/get-pin', [PinController::class, 'getPinCode']);
Route::post('/pin-handle-subscription', [PinController::class, 'handleSubscription']);

/*
|--------------------------------------------------------------------------
| Distinguished Subscription Routes
|--------------------------------------------------------------------------
*/
