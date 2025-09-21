<?php

namespace App\Application\Timers\Services;

use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Tasks\Exceptions\NotOwnerOfTask;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\NoActiveTimerOnTask;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

final class TimerService
{
    public function __construct(
        private readonly TimerRepositoryInterface $timers,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TransactionRunner $tx,
    ) {}

    public function start(string $task_id, int $user_id): Timer
    {
        return $this->tx->run(function () use ($task_id, $user_id) {
            if (! $this->taskRepository->userOwnsTask($task_id, $user_id)) {
                throw new NotOwnerOfTask();
            }

            $active = $this->timers->findActiveForUserLock($user_id, $task_id);
            if ($active) {
                throw new ActiveTimerExists((string) $active->id);
            }

            return $this->timers->createRunning($task_id, $user_id);
        });
    }

    public function stop(string $taskId, int $userId): Timer
    {
        return $this->tx->run(function () use ($taskId, $userId) {
            if (! $this->taskRepository->userOwnsTask($taskId, $userId)) {
                throw new NotOwnerOfTask();
            }

            $stopped = $this->timers->stopActiveForUserOnTask($taskId, $userId);
            if (! $stopped) {
                throw new NoActiveTimerOnTask();
            }

            return $stopped;
        });
    }

    public function activeForUser(int $userId): ?Timer
    {
        return $this->timers->findActiveWithContext($userId);
    }
}
