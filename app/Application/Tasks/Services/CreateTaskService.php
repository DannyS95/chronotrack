<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class CreateTaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function handle(CreateTaskDTO $dto): mixed
    {
        return $this->taskRepository->create($dto->toArray());
    }
}
