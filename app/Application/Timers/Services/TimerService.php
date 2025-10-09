<?php

namespace App\Application\Timers\Services;

use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
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
                if ($activeForTask->paused_at !== null) {
                    return $this->timerRepository->resumeTimer($activeForTask);
                }

                return $activeForTask;
            }

            $runningTimers = $this->timerRepository->findRunningTimersForUser($userId, $task->id);

            foreach ($runningTimers as $runningTimer) {
                $otherTask = optional($runningTimer->task);
                $otherProjectId = $otherTask?->project_id;
                $otherGoalId = $otherTask?->goal_id;

                $sameProject = $otherProjectId !== null
                    && (string) $otherProjectId === (string) $task->project_id;

                if ($sameProject) {
                    $scope = ($task->goal_id !== null && $task->goal_id === $otherGoalId)
                        ? 'goal'
                        : 'project';

                    throw new ActiveTimerExists((string) $runningTimer->id, $scope);
                }

                $this->timerRepository->pauseActiveTimerForTask($runningTimer->task_id);
            }

            return $this->timerRepository->createRunning($task->id, $userId);
        });
    }

    public function pause(Task $task, int|string $userId): Timer
    {
        return $this->tx->run(function () use ($task) {
            if ($task->status === 'done') {
                throw ValidationException::withMessages([
                    'task_id' => ['Cannot pause a timer on a completed task.'],
                ]);
            }

            $paused = $this->timerRepository->pauseActiveTimerForTask($task->id);
            if (! $paused) {
                throw new NoActiveTimerOnTask();
            }

            return $paused;
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
