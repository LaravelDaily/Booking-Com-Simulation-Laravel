<?php

use Illuminate\Support\Facades\Route;

Route::post('auth/register', \App\Http\Controllers\Auth\RegisterController::class);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('owner/properties',
        [\App\Http\Controllers\Owner\PropertyController::class, 'index']);
    Route::post('owner/properties',
        [\App\Http\Controllers\Owner\PropertyController::class, 'store']);

    Route::get('user/bookings',
        [\App\Http\Controllers\User\BookingController::class, 'index']);

});
