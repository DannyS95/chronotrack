<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\ViewModels\TaskViewModel;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Auth\Access\AuthorizationException;

final class ShowTaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(Project $project, Task $task, string $userId): TaskViewModel
    {
        if ($task->project_id !== $project->id) {
            throw new AuthorizationException('Task does not belong to the provided project.');
        }

        $snapshot = $this->taskRepository->findSnapshot($task->id, $project->id, $userId);

        return TaskViewModel::fromSnapshot($snapshot);
    }
}
