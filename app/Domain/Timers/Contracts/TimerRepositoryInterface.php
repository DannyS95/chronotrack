<?php

namespace App\Domain\Timers\Contracts;

use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TimerRepositoryInterface
{
    public function findActiveForTaskLock(string $taskId): ?Timer;

    public function findActiveForGoalLock(string $goalId, string $userId, ?string $excludingTaskId = null): ?Timer;

    public function findActiveWithoutGoalForUserLock(string $userId, ?string $excludingTaskId = null): ?Timer;

    public function createRunning(string $taskId): Timer;

    public function stopActiveTimerForTask(string $taskId): ?Timer;

    /** @param array<int, string> $taskIds */
    public function stopActiveTimersForTasks(array $taskIds): int;

    public function findActiveWithContext(string $userId): ?Timer;

    public function list(array $filters): LengthAwarePaginator;
}
