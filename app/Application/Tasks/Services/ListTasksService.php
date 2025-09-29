<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Application\Tasks\ViewModels\TaskCollectionViewModel;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class ListTasksService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function handle(TaskFilterDTO $dto): TaskCollectionViewModel
    {
        $paginator = $this->taskRepository->paginateSnapshots(
            $dto->toArray(),
            $dto->user_id,
            $dto->project_id,
            $dto->per_page,
        );

        return TaskCollectionViewModel::fromPaginator($paginator);
    }
}
