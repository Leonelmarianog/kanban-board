<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Email verification: 3 per 15 min per email, 10 per hour per IP
        RateLimiter::for('verification-email', function (Request $request) {
            return [
                Limit::perMinutes(15, 3)->by($request->email),
                Limit::perHour(10)->by($request->ip()),
            ];
        });

        // Email change initiate: 3 per 15 min per user/IP, 10 per hour per IP
        RateLimiter::for('email-change', function (Request $request) {
            return [
                Limit::perMinutes(15, 3)->by($request->user()->id ?? $request->ip()),
                Limit::perHour(10)->by($request->ip()),
            ];
        });

        // Email change confirm: 10 per minute per IP
        RateLimiter::for('email-change-confirm', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Email change cancel: 10 per minute per IP
        RateLimiter::for('email-change-cancel', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
