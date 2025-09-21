<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListTasksService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Handle the task listing with filters.
     *
     * @return LengthAwarePaginator<Task>
     */
    public function handle(TaskFilterDTO $dto): LengthAwarePaginator
    {
        /** @var Builder<Task> $query */
        $query = $this->taskRepository->getFiltered($dto->toArray(), $dto->user_id);

        return $query->paginate($dto->per_page);
    }
}
