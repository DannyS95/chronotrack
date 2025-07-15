<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Tasks\Repositories\TaskRepository;

final class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
