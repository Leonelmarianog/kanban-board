<?php

use Illuminate\Support\Facades\Route;
use Modules\Infrastructure\Http\Controllers\Auth\LoginUserController;
use Modules\Infrastructure\Http\Controllers\Auth\LogoutUserController;
use Modules\Infrastructure\Http\Controllers\Auth\RegisterUserController;
use Modules\Infrastructure\Http\Controllers\Auth\SendVerificationEmailController;
use Modules\Infrastructure\Http\Controllers\Auth\VerifyEmailController;

/* Base API routes that don't need versioning like login and register go here. */

Route::post('auth/register', RegisterUserController::class);
Route::post('auth/login', LoginUserController::class);

// Email verification routes
Route::post('auth/email-verification/send', SendVerificationEmailController::class)
    ->middleware('throttle:verification-email')
    ->name('verification.send');

Route::post('auth/email-verification/verify', VerifyEmailController::class)
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', LogoutUserController::class);
});
