<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Tasks\Repositories\TaskRepository;

final class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
    }

    public function boot(): void
    {
        Route::bind('task', function (string $value) {
            /** @var \Illuminate\Contracts\Auth\Guard $auth */
            $auth = auth();
            $userId = $auth->id();

            $projectParam = request()->route('project');
            $projectId = is_object($projectParam) ? $projectParam->id : $projectParam;

            return Task::query()
                ->where('id', $value)
                ->whereHas('project', function ($query) use ($userId, $projectId) {
                    $query->where('user_id', $userId);

                    if ($projectId) {
                        $query->where('id', $projectId);
                    }
                })
                ->firstOrFail();
        });
    }
}
