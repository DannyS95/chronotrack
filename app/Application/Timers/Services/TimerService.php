<?php

namespace App\Application\Timers\Services;

use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Tasks\Exceptions\NotOwnerOfTask;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\ActiveTimerWithinGoalException;
use App\Domain\Timers\Exceptions\NoActiveTimerOnTask;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

final class TimerService
{
    public function __construct(
        private readonly TimerRepositoryInterface $timers,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TransactionRunner $tx,
    ) {}

    public function start(Task $task, string $userId): Timer
    {
        return $this->tx->run(function () use ($task, $userId) {
            # double check user owns task
            if (! $this->taskRepository->userOwnsTask($task->id, $userId)) {
                throw new NotOwnerOfTask();
            }

            $activeForTask = $this->timers->findActiveForTaskLock($task->id);
            if ($activeForTask) {
                throw new ActiveTimerExists((string) $activeForTask->id);
            }

            if ($task->goal_id) {
                $activeWithinGoal = $this->timers->findActiveForGoalLock($task->goal_id, $userId, $task->id);

                if ($activeWithinGoal) {
                    throw new ActiveTimerWithinGoalException((string) $activeWithinGoal->id);
                }
            } else {
                $activeWithoutGoal = $this->timers->findActiveWithoutGoalForUserLock($userId, $task->id);

                if ($activeWithoutGoal) {
                    throw new ActiveTimerExists((string) $activeWithoutGoal->id);
                }
            }

            return $this->timers->createRunning($task->id);
        });
    }

    public function stop(Task $task, string $userId): Timer
    {
        return $this->tx->run(function () use ($task, $userId) {
            if (! $this->taskRepository->userOwnsTask($task->id, $userId)) {
                throw new NotOwnerOfTask();
            }

            $stopped = $this->timers->stopActiveTimerForTask($task->id);
            if (! $stopped) {
                throw new NoActiveTimerOnTask();
            }

            return $stopped;
        });
    }

    public function activeForUser(string $userId): ?Timer
    {
        return $this->timers->findActiveWithContext($userId);
    }
}
