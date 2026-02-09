<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\ViewModels\TaskViewModel;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class CreateTaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(CreateTaskDTO $dto): TaskViewModel
    {
        $snapshot = $this->taskRepository->createSnapshot($dto->toArray());

        return TaskViewModel::fromSnapshot($snapshot);
    }
}
