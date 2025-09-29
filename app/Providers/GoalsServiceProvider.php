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
        Route::bind('goal', function (string $value) {
            /** @var \Illuminate\Contracts\Auth\Guard $auth */
            $auth = auth();
            $projectParam = request()->route('project');
            $projectId = is_object($projectParam) ? $projectParam->id : $projectParam;

            return Goal::query()
                ->where('id', $value)
                ->when($projectId, fn($query) => $query->where('project_id', $projectId))
                ->whereHas('project', fn($query) => $query->where('user_id', $auth->id()))
                ->firstOrFail();
        });
    }
}
