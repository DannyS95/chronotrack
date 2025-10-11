<?php

namespace App\Domain\Timers\Contracts;

use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TimerRepositoryInterface
{
    public function findActiveForTaskLock(string $taskId): ?Timer;

    public function findActiveForGoalLock(string $goalId, string $userId, ?string $excludingTaskId = null): ?Timer;

    /** @return Collection<int, Timer> */
    public function findRunningTimersForUser(string $userId, ?string $excludingTaskId = null): Collection;

    public function findActiveTimerForUserLock(string $userId): ?Timer;

    public function findTimerById(string $timerId): ?Timer;

    public function createRunning(string $taskId, int|string $userId): Timer;

    public function pauseActiveTimerForTask(string $taskId): ?Timer;

    public function resumeTimer(Timer $timer): Timer;

    public function stopActiveTimerForTask(string $taskId): ?Timer;

    /** @param array<int, string> $taskIds */
    public function stopActiveTimersForTasks(array $taskIds): int;

    /** @param array<int, string> $taskIds */
    public function deleteTimersForTasks(array $taskIds): int;

    public function findActiveWithContext(string $userId): ?Timer;

    public function list(array $filters): LengthAwarePaginator;

    public function countActiveByProject(string $projectId, string $userId): int;
}
