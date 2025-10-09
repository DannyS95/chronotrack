<?php

namespace App\Infrastructure\Timers\Persistence\Eloquent;

use App\Domain\Common\Enums\ComparisonOperator;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class TimerRepository implements TimerRepositoryInterface
{
    public function findActiveForTaskLock(string $taskId): ?Timer
    {
        return Timer::query()
            ->with('task')
            ->where('task_id', $taskId)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->lockForUpdate()
            ->first();
    }

    public function findActiveForGoalLock(string $goalId, string $userId, ?string $excludingTaskId = null): ?Timer
    {
        return Timer::query()
            ->with('task')
            ->whereNull('stopped_at')
            ->whereHas('task', function (Builder $query) use ($goalId, $userId, $excludingTaskId) {
                $query->where('goal_id', $goalId)
                    ->when($excludingTaskId, fn(Builder $q) => $q->where('id', ComparisonOperator::NotEqual->value, $excludingTaskId))
                    ->whereHas('project', fn($projectQuery) => $projectQuery->where('user_id', $userId));
            })
            ->latest('started_at')
            ->lockForUpdate()
            ->first();
    }

    public function findRunningTimersForUser(string $userId, ?string $excludingTaskId = null): Collection
    {
        return Timer::query()
            ->with('task')
            ->whereNull('stopped_at')
            ->when($excludingTaskId, fn($query) => $query->where('task_id', ComparisonOperator::NotEqual->value, $excludingTaskId))
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere(function ($query) use ($userId) {
                        $query->whereNull('user_id')
                            ->whereHas('task.project', fn($projectQuery) => $projectQuery->where('user_id', $userId));
                    });
            })
            ->lockForUpdate()
            ->get();
    }

    public function createRunning(string $taskId, int|string $userId): Timer
    {
        $timer = new Timer();
        $timer->task_id = $taskId;
        $timer->user_id = $userId;
        $timer->started_at = now();
        $timer->paused_at = null;
        $timer->paused_total = 0;
        $timer->save();

        return $timer->fresh('task');
    }

    public function pauseActiveTimerForTask(string $taskId): ?Timer
    {
        $timer = Timer::query()
            ->where('task_id', $taskId)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->lockForUpdate()
            ->first();

        if (! $timer) {
            return null;
        }

        if ($timer->paused_at === null) {
            $timer->paused_at = now();
            $timer->save();
        }

        return $timer->fresh('task');
    }

    public function resumeTimer(Timer $timer): Timer
    {
        if ($timer->paused_at === null) {
            return $timer;
        }

        $pausedAt = Carbon::parse($timer->paused_at);
        $now = Carbon::now();
        $timer->paused_total = (int) $timer->paused_total + max(0, $pausedAt->diffInSeconds($now));
        $timer->paused_at = null;
        $timer->save();

        return $timer->fresh('task');
    }

    public function stopActiveTimerForTask(string $taskId): ?Timer
    {
        $row = Timer::query()
            ->where('task_id', $taskId)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->lockForUpdate()
            ->first();

        if (! $row) {
            return null;
        }

        $now = Carbon::now();

        $effectiveStop = $row->paused_at ?? $now;

        $row->stopped_at = $effectiveStop;
        $row->paused_at = null;

        if ($row->started_at) {
            $startedAt = Carbon::parse($row->started_at);
            $seconds = $startedAt->floatDiffInSeconds($effectiveStop, false);
            $row->duration = max(0, (int) round($seconds));
        }
        $row->save();

        return $row;
    }

    public function stopActiveTimersForTasks(array $taskIds): int
    {
        if ($taskIds === []) {
            return 0;
        }

        $timers = Timer::query()
            ->whereIn('task_id', $taskIds)
            ->whereNull('stopped_at')
            ->get();

        if ($timers->isEmpty()) {
            return 0;
        }

        $now = Carbon::now();

        foreach ($timers as $timer) {
            $timer->stopped_at = $now;

            if ($timer->started_at) {
                $startedAt = Carbon::parse($timer->started_at);
                $seconds = $startedAt->floatDiffInSeconds($now, false);
                $timer->duration = max(0, (int) round($seconds));
            }
            $timer->save();
        }

        return $timers->count();
    }

    public function deleteTimersForTasks(array $taskIds): int
    {
        if ($taskIds === []) {
            return 0;
        }

        return Timer::query()
            ->whereIn('task_id', $taskIds)
            ->delete();
    }

    public function findActiveWithContext(string $userId): ?Timer
    {
        return Timer::query()
            ->with(['task:id,title,project_id', 'task.project:id,name'])
            ->whereNull('stopped_at')
            ->whereHas('task.project', fn($q) => $q->where('user_id', $userId))
            ->latest('started_at')
            ->first();
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return Timer::applyFilters($filters)
            ->with('task')
            ->orderByDesc('started_at')
            ->paginate(20);
    }
}
