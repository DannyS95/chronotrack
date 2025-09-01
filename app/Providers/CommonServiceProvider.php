<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Infrastructure\Support\LaravelTransactionRunner;

class CommonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionRunner::class, LaravelTransactionRunner::class);
    }
}
