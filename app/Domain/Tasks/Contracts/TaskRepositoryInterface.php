<?php

namespace App\Domain\Tasks\Contracts;

use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function createSnapshot(array $data): TaskSnapshot;

    public function paginateSnapshots(array $filters, string $userId, string $projectId, int $perPage): LengthAwarePaginator;

    public function userOwnsTask(string $taskId, string $userId): bool;

    public function findOwned(string $taskId, string $projectId, string $userId): Task;

    public function updateGoal(Task $task, ?string $goalId): void;

    /** @return Collection<int, TaskSnapshot> */
    public function getSnapshotsByGoal(string $goalId, string $projectId, string $userId): Collection;
}
