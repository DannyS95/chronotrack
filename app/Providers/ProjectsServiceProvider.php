<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Infrastructure\Projects\Persistence\Eloquent\ProjectRepository;

class ProjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectRepositoryInterface::class,
            ProjectRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
