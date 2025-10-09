<?php

namespace App\Application\Timers\Services;

use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\NoActiveTimerOnTask;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class TimerService
{
    public function __construct(
        private readonly TimerRepositoryInterface $timerRepository,
        private readonly TransactionRunner $tx,
    ) {}

    private const IDEMPOTENCY_CACHE_PREFIX = 'timers:stop';
    private const IDEMPOTENCY_TTL_SECONDS = 300;
    private const NO_TIMER_MARKER = '__none__';

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

    public function stop(Task $task, string $userId, ?string $idempotencyKey = null): ?Timer
    {
        if ($idempotencyKey) {
            $cached = $this->retrieveCachedStop($userId, $idempotencyKey, $task->id);
            if ($cached !== null) {
                return $cached;
            }
        }

        $timer = $this->tx->run(fn() => $this->timerRepository->stopActiveTimerForTask($task->id));

        if ($idempotencyKey) {
            $this->cacheStopResult($userId, $idempotencyKey, $task->id, $timer);
        }

        return $timer;
    }

    public function stopCurrent(string $userId, ?string $idempotencyKey = null): ?Timer
    {
        $scopeKey = $idempotencyKey ? $idempotencyKey.'_current' : null;

        if ($scopeKey) {
            $cached = $this->retrieveCachedStop($userId, $scopeKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $timer = $this->tx->run(function () use ($userId) {
            $active = $this->timerRepository->findActiveTimerForUserLock($userId);

            if (! $active) {
                return null;
            }

            return $this->timerRepository->stopActiveTimerForTask($active->task_id);
        });

        if ($scopeKey) {
            $this->cacheStopResult($userId, $scopeKey, null, $timer);
        }

        return $timer;
    }

    public function activeForUser(string $userId): ?Timer
    {
        return $this->timerRepository->findActiveWithContext($userId);
    }

    private function retrieveCachedStop(string $userId, string $idempotencyKey, ?string $taskId = null): ?Timer
    {
        $cacheKey = $this->cacheKey($userId, $idempotencyKey, $taskId);
        if (! Cache::has($cacheKey)) {
            return null;
        }

        $value = Cache::get($cacheKey);

        if ($value === self::NO_TIMER_MARKER) {
            return null;
        }

        return $this->timerRepository->findTimerById($value);
    }

    private function cacheStopResult(string $userId, string $idempotencyKey, ?string $taskId, ?Timer $timer): void
    {
        $cacheKey = $this->cacheKey($userId, $idempotencyKey, $taskId);
        Cache::put(
            $cacheKey,
            $timer?->id ?? self::NO_TIMER_MARKER,
            self::IDEMPOTENCY_TTL_SECONDS
        );
    }

    private function cacheKey(string $userId, string $idempotencyKey, ?string $taskId = null): string
    {
        $scope = $taskId ? 'task:'.$taskId : 'current';

        return sprintf('%s:%s:%s', self::IDEMPOTENCY_CACHE_PREFIX, $userId, $scope.'::'.$idempotencyKey);
    }
}
