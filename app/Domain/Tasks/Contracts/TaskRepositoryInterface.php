<?php

namespace App\Domain\Tasks\Contracts;

use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function create(array $data): mixed;

    public function getFiltered(array $filters, string $user_id, string $project_id): Builder;

    public function userOwnsTask(string $taskId, string $userId): bool;

    public function findOwned(string $taskId, string $projectId, string $userId): Task;

    public function updateGoal(Task $task, ?string $goalId): void;

    public function getByGoal(string $goalId, string $projectId, string $userId): Collection;
}
