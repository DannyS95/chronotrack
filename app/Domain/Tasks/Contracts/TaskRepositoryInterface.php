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

    public function findOwned(string $taskId, string $projectId, string $userId): Task;

    public function findSnapshot(string $taskId, string $projectId, string $userId): TaskSnapshot;

    public function updateGoal(Task $task, ?string $goalId): void;

    /** @return Collection<int, TaskSnapshot> */
    public function getSnapshotsByGoal(string $goalId, string $projectId, string $userId): Collection;

    /** @param array<string, mixed> $attributes */
    public function updateSnapshot(Task $task, array $attributes): TaskSnapshot;

    public function delete(Task $task): void;

    /** @return Collection<int, Task> */
    public function getTasksByGoal(string $goalId, string $projectId, string $userId): Collection;

    /** @param array<int, string> $taskIds */
    public function markTasksAsComplete(array $taskIds): int;

    public function countIncompleteByGoal(string $goalId, string $projectId, string $userId): int;

    /** @return Collection<int, Task> */
    public function getTasksByProject(string $projectId, string $userId): Collection;

    public function countByProject(string $projectId, string $userId): int;

    public function countIncompleteByProject(string $projectId, string $userId): int;
}
