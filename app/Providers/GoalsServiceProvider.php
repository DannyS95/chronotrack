<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Goals\Repositories\GoalRepository;

class GoalsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GoalRepositoryInterface::class, GoalRepository::class);
    }

    public function boot(): void
    {
        Route::model('goal', Goal::class);
    }
}
