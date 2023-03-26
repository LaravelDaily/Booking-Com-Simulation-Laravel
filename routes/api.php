<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public;

Route::post('auth/register', \App\Http\Controllers\Auth\RegisterController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('owner')->group(function () {
        Route::get('properties',
            [\App\Http\Controllers\Owner\PropertyController::class, 'index']);
        Route::post('properties',
            [\App\Http\Controllers\Owner\PropertyController::class, 'store']);
        Route::post('properties/{property}/photos',
            [\App\Http\Controllers\Owner\PropertyPhotoController::class, 'store']);
        Route::post('properties/{property}/photos/{photo}/reorder/{newPosition}',
            [\App\Http\Controllers\Owner\PropertyPhotoController::class, 'reorder']);
    });

    Route::prefix('user')->group(function () {
        Route::resource('bookings', \App\Http\Controllers\User\BookingController::class)->withTrashed();
    });
});

Route::get('search', Public\PropertySearchController::class);
Route::get('properties/{property}', Public\PropertyController::class);
Route::get('apartments/{apartment}', Public\ApartmentController::class);
