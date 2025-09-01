<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Timers\Persistence\Eloquent\TimerRepository;

class TimersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TimerRepositoryInterface::class, TimerRepository::class);
    }
}
