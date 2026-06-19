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
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email'))),
        ]);

        RateLimiter::for('password-email', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip().'|'.strtolower((string) $request->input('email'))),
        ]);

        RateLimiter::for('public-orders', fn (Request $request) => [
            Limit::perMinute(8)->by($request->ip()),
            Limit::perHour(40)->by($request->ip()),
        ]);

        RateLimiter::for('order-status', fn (Request $request) => [
            Limit::perMinute(60)->by($request->ip()),
        ]);

        RateLimiter::for('catalog', fn (Request $request) => [
            Limit::perMinute(120)->by($request->ip()),
        ]);

        RateLimiter::for('admin-actions', fn (Request $request) => [
            Limit::perMinute(80)->by((string) $request->user()?->getAuthIdentifier() ?: $request->ip()),
        ]);

        RateLimiter::for('admin-exports', fn (Request $request) => [
            Limit::perMinute(10)->by((string) $request->user()?->getAuthIdentifier() ?: $request->ip()),
        ]);
    }
}
