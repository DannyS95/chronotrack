<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class ListTasksService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function handle(TaskFilterDTO $dto): mixed
    {
        return $this->taskRepository->getFiltered($dto)->paginate($dto->per_page);
    }
}
