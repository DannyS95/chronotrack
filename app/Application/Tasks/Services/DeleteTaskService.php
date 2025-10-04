<?php

namespace App\Application\Tasks\Services;

use App\Application\Projects\Services\ProjectLifecycleService;
use App\Application\Tasks\DTO\DeleteTaskDTO;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class DeleteTaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private ProjectLifecycleService $projectLifecycleService,
    ) {}

    public function handle(DeleteTaskDTO $dto): void
    {
        $task = $this->taskRepository->findOwned(
            $dto->task_id,
            $dto->project_id,
            $dto->user_id,
        );

        $this->taskRepository->delete($task);

        $this->projectLifecycleService->refresh($dto->project_id, $dto->user_id);
    }
}
