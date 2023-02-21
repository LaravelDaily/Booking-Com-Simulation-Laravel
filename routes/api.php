<?php

use Illuminate\Support\Facades\Route;

Route::post('auth/register', \App\Http\Controllers\Auth\RegisterController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('owner')->group(function () {
        Route::get('properties',
            [\App\Http\Controllers\Owner\PropertyController::class, 'index']);
        Route::post('properties',
            [\App\Http\Controllers\Owner\PropertyController::class, 'store']);
    });

    Route::prefix('user')->group(function () {
        Route::get('bookings',
            [\App\Http\Controllers\User\BookingController::class, 'index']);
        Route::get('search',
            \App\Http\Controllers\User\PropertySearchController::class);
    });

});
