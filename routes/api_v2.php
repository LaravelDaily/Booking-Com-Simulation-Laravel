<?php

use App\Http\Controllers\Api\V2\Auth\RegisterController;
use App\Http\Controllers\Api\V2\Owner\PropertyController;
use App\Http\Controllers\Api\V2\Owner\PropertyPhotoController;
use App\Http\Controllers\Api\V2\Public;
use App\Http\Controllers\Api\V2\User\BookingController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', RegisterController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('owner')->group(function () {
        Route::get('properties',
            [PropertyController::class, 'index']);
        Route::post('properties',
            [PropertyController::class, 'store']);
        Route::post('properties/{property}/photos',
            [PropertyPhotoController::class, 'store']);
        Route::post('properties/{property}/photos/{photo}/reorder/{newPosition}',
            [PropertyPhotoController::class, 'reorder']);
    });

    Route::prefix('user')->group(function () {
        Route::resource('bookings', BookingController::class)->withTrashed();
    });
});

Route::get('search', Public\PropertySearchController::class);
Route::get('properties/{property}', Public\PropertyController::class);
Route::get('apartments/{apartment}', Public\ApartmentController::class);
