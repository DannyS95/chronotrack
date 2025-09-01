<?php

namespace App\Domain\Timers\Contracts;

use App\Infrastructure\Timers\Eloquent\Models\Timer;

interface TimerRepositoryInterface
{
    public function findActiveForUserLock(int $userId): ?Timer;
    public function createRunning(string $taskId, int $userId): Timer;
    public function stopActiveForUserOnTask(string $taskId, int $userId): ?Timer;
    public function findActiveWithContext(int $userId): ?Timer;
}
