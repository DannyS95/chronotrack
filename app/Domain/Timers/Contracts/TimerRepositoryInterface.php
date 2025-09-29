<?php

namespace App\Domain\Timers\Contracts;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TimerRepositoryInterface
{
    public function findActiveForUserLock(string $userId, string $task_id): ?Timer;

    public function createRunning(string $taskId): Timer;

    public function stopActiveForUserOnTask(string $taskId, string $userId): ?Timer;

    public function findActiveWithContext(string $userId): ?Timer;

    public function list(array $filters): LengthAwarePaginator;
}
