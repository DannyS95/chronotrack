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

    public function getFiltered(array $filters, string $user_id): Builder
    {
        return Project::query()
            ->where('user_id', $user_id)
            ->firstOrFail()
            ->tasks()
            ->tap(fn($query) => Task::applyFilters($filters)
            ->mergeConstraintsFrom($query->getModel()->newEloquentBuilder($query->getQuery())));
    }

    public function userOwnsTask(string $taskId, int $userId): bool
    {
        return Task::query()
            ->where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }
}
