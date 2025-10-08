<?php

namespace App\Application\Timers\Services;

use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\ActiveTimerWithinGoalException;
use App\Domain\Timers\Exceptions\NoActiveTimerOnTask;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Validation\ValidationException;

final class TimerService
{
    public function __construct(
        private readonly TimerRepositoryInterface $timerRepository,
        private readonly TransactionRunner $tx,
    ) {}

    public function start(Task $task, int|string $userId): Timer
    {
        return $this->tx->run(function () use ($task, $userId) {
            if ($task->status === 'done') {
                throw ValidationException::withMessages([
                    'task_id' => ['Cannot start a timer on a completed task.'],
                ]);
            }

            $activeForTask = $this->timerRepository->findActiveForTaskLock($task->id);
            if ($activeForTask) {
                throw new ActiveTimerExists((string) $activeForTask->id);
            }

            if ($task->goal_id) {
                $activeWithinGoal = $this->timerRepository->findActiveForGoalLock($task->goal_id, $userId, $task->id);

                if ($activeWithinGoal) {
                    throw new ActiveTimerWithinGoalException((string) $activeWithinGoal->id);
                }
            } else {
                $activeWithoutGoal = $this->timerRepository->findActiveWithoutGoalForUserLock($userId, $task->id);

                if ($activeWithoutGoal) {
                    throw new ActiveTimerExists((string) $activeWithoutGoal->id);
                }
            }

            return $this->timerRepository->createRunning($task->id, $userId);
        });
    }

    public function stop(Task $task, string $userId): Timer
    {
        return $this->tx->run(function () use ($task, $userId) {
            $stopped = $this->timerRepository->stopActiveTimerForTask($task->id);
            if (! $stopped) {
                throw new NoActiveTimerOnTask();
            }

            return $stopped;
        });
    }

    public function activeForUser(string $userId): ?Timer
    {
        return $this->timerRepository->findActiveWithContext($userId);
    }
}
