<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Infrastructure\Goals\Repositories\GoalRepository;

class GoalsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GoalRepositoryInterface::class, GoalRepository::class);
    }
}
