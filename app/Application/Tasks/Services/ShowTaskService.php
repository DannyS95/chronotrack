<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\ViewModels\TaskViewModel;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Tasks\Eloquent\Models\Task;

final class ShowTaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(Task $task, string $userId): TaskViewModel
    {
        $snapshot = $this->taskRepository->findSnapshot($task->id, (string) $task->project_id, $userId);

        return TaskViewModel::fromSnapshot($snapshot);
    }
}
