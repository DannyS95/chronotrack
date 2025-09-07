<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Contracts\Database\Eloquent\Builder;

final class TaskRepository implements TaskRepositoryInterface
{
    public function create(CreateTaskDTO $dto): Task
    {
        return Task::create([
            'project_id' => $dto->project_id,
            'title' => $dto->title,
            'description' => $dto->description,
            'due_at' => $dto->due_at,
        ]);
    }

    public function getFiltered(TaskFilterDTO $dto): Builder
    {
        $filters = [
            'project_id' => $dto->project_id,
            'title' => $dto->title,
            'from' => $dto->from,
            'to' => $dto->to,
            'sort_by' => $dto->sort_by,
            'order' => $dto->order,
        ];

        return Project::query()
            ->where('id', $dto->project_id)
            ->where('user_id', $dto->user_id)
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
