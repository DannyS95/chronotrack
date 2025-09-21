<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Contracts\Database\Eloquent\Builder;

final class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function getFiltered(array $filters, string $user_id, string $project_id): Builder
    {
        // Ensure the project exists and belongs to the user
        $project = Project::query()
            ->where('id', $project_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        return Task::filters($filters)
            ->where('project_id', $project->id)
            ->whereHas('project', fn($q) => $q->where('user_id', $user_id));
    }

    public function userOwnsTask(string $taskId, int $userId): bool
    {
        return Task::query()
            ->where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }
}
