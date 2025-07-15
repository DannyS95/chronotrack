<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Infrastructure\Tasks\Eloquent\Models\Task;

final class TaskRepository implements TaskRepositoryInterface
{
    public function create(CreateTaskDTO $dto): Task
    {
        return Task::create([
            'project_id' => $dto->projectId,
            'title' => $dto->title,
            'description' => $dto->description,
            'due_at' => $dto->due_at,
        ]);
    }
}
