<?php

use Illuminate\Support\Facades\Route;
use Modules\Infrastructure\Http\Controllers\Member\GetMemberController;
use Modules\Infrastructure\Http\Controllers\Member\UpdateProfileDetailsController;

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return 'Hello World from V1!';
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Members
        Route::get('/members/me', GetMemberController::class);
        Route::put('/members/me/profile', UpdateProfileDetailsController::class);
    });
});
