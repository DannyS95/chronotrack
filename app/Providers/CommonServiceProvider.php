<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Common\Contracts\Clock;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Infrastructure\Support\LaravelTransactionRunner;
use App\Infrastructure\Support\SystemClock;

class CommonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Clock::class, SystemClock::class);
        $this->app->bind(TransactionRunner::class, LaravelTransactionRunner::class);
    }
}
