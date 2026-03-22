<?php

use Illuminate\Support\Facades\Route;
use Modules\Infrastructure\Http\Controllers\Auth\LoginUserController;
use Modules\Infrastructure\Http\Controllers\Auth\LogoutUserController;
use Modules\Infrastructure\Http\Controllers\Auth\RegisterUserController;

/* Base API routes that don't need versioning like login and register go here. */

Route::post('auth/register', RegisterUserController::class);
Route::post('auth/login', LoginUserController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', LogoutUserController::class);
});
