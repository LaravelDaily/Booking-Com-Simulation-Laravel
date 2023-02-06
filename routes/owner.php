<?php

use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->group(function() {
    Route::post('auth/register', \App\Http\Controllers\Auth\RegisterController::class);
});
