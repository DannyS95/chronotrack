<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('auth-login', fn(Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('auth-register', fn(Request $request) => Limit::perMinute(6)->by($request->ip()));

        RateLimiter::for('auth-session', fn(Request $request) => Limit::perMinute(120)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('projects', fn(Request $request) => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('tasks', fn(Request $request) => Limit::perMinute(90)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('goals', fn(Request $request) => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('timer-actions', fn(Request $request) => Limit::perMinute(120)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('timers', fn(Request $request) => Limit::perMinute(120)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
    }
}
