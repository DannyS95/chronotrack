<?php

namespace App\Application\Tasks\Services;

use App\Application\Projects\Services\ProjectLifecycleService;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\ViewModels\TaskViewModel;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class CreateTaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private ProjectLifecycleService $projectLifecycleService,
    ) {}

    public function handle(CreateTaskDTO $dto): TaskViewModel
    {
        $snapshot = $this->taskRepository->createSnapshot($dto->toArray());

        $this->projectLifecycleService->refresh($dto->project_id, $dto->user_id);

        return TaskViewModel::fromSnapshot($snapshot);
    }
}
