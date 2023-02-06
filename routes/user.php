<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User;

Route::prefix('user')->group(function() {
    Route::post('auth/register', User\Auth\RegisterController::class);
});
