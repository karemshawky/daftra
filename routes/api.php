<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {

    # Login
    Route::post('/login', [Auth\LoginController::class, 'login']);

    # Dashboard Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        //
    });
});
