<?php

namespace App\Domain\Timers\Contracts;

use App\Application\Timers\DTOs\TimerFilterDTO;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TimerRepositoryInterface
{
    public function findActiveForUserLock(int $userId): ?Timer;

    public function createRunning(string $taskId, int $userId): Timer;

    public function stopActiveForUserOnTask(string $taskId, int $userId): ?Timer;

    public function findActiveWithContext(int $userId): ?Timer;

    public function list(array $filters): LengthAwarePaginator;
}
