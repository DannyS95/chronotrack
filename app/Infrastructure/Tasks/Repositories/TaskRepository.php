<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Infrastructure\Tasks\Eloquent\Models\Task;

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

    public function getFiltered(TaskFilterDTO $dto): mixed
    {
        $filters = [
            'project_id' => $dto->project_id,
            'title' => $dto->title,
            'from' => $dto->from,
            'to' => $dto->to,
            'sort_by' => $dto->sort_by,
            'order' => $dto->order,
        ];

        return Task::applyFilters($filters)
            ->whereHas('project', function ($query) use ($dto) {
                $query->where('user_id', $dto->user_id);
            })
            ->get();
    }
}
