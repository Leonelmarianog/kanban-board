<?php

use Illuminate\Support\Facades\Route;
use Modules\Infrastructure\Http\Controllers\Auth\CancelEmailChangeController;
use Modules\Infrastructure\Http\Controllers\Auth\ChangeEmailController;
use Modules\Infrastructure\Http\Controllers\Auth\ChangePasswordController;
use Modules\Infrastructure\Http\Controllers\Auth\ConfirmEmailChangeController;
use Modules\Infrastructure\Http\Controllers\Auth\LoginUserController;
use Modules\Infrastructure\Http\Controllers\Auth\LogoutUserController;
use Modules\Infrastructure\Http\Controllers\Auth\RegisterUserController;
use Modules\Infrastructure\Http\Controllers\Auth\SendVerificationEmailController;
use Modules\Infrastructure\Http\Controllers\Auth\VerifyEmailController;

/* Base API routes that don't need versioning like login and register go here. */

Route::post('auth/register', RegisterUserController::class);
Route::post('auth/login', LoginUserController::class);

// Email verification routes
// Throttle: 3 per 15 min per email, 10 per hour per IP
Route::post('auth/email-verification/send', SendVerificationEmailController::class)
    ->middleware('throttle:verification-email')
    ->name('verification.send');

Route::post('auth/email-verification/verify', VerifyEmailController::class)
    ->name('verification.verify');

// Email change routes
// Throttle: 10 per minute per IP
Route::post('auth/email-change/confirm', ConfirmEmailChangeController::class)
    ->middleware('throttle:email-change-confirm')
    ->name('email-change.confirm');

// Throttle: 10 per minute per IP
Route::post('auth/email-change/cancel', CancelEmailChangeController::class)
    ->middleware('throttle:email-change-cancel')
    ->name('email-change.cancel');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', LogoutUserController::class);

    // Throttle: 3 per 15 min per user/IP, 10 per hour per IP
    Route::post('auth/email-change', ChangeEmailController::class)
        ->middleware('throttle:email-change');

    // Throttle: 3 per 15 min per user
    Route::patch('auth/change-password', ChangePasswordController::class)
        ->middleware('throttle:password-change');
});
