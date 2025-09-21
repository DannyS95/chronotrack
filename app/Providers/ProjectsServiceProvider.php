<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Projects\Persistence\Eloquent\ProjectRepository;
use Illuminate\Support\Facades\Route;

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
        Route::bind('project', function (string $value) {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
            return Project::query()
                ->where('id', $value)
                ->where('user_id', $auth->user()->id)
                ->firstOrFail();
        });
    }
}
