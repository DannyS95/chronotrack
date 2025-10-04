<?php

namespace App\Infrastructure\Timers\Persistence\Eloquent;

use App\Domain\Common\Enums\ComparisonOperator;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

final class TimerRepository implements TimerRepositoryInterface
{
    public function findActiveForTaskLock(string $taskId): ?Timer
    {
        return Timer::query()
            ->where('task_id', $taskId)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->lockForUpdate()
            ->first();
    }

    public function findActiveForGoalLock(string $goalId, string $userId, ?string $excludingTaskId = null): ?Timer
    {
        return Timer::query()
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

    public function findActiveWithoutGoalForUserLock(string $userId, ?string $excludingTaskId = null): ?Timer
    {
        return Timer::query()
            ->whereNull('stopped_at')
            ->whereHas('task', function ($query) use ($userId, $excludingTaskId) {
                $query->whereNull('goal_id')
                    ->when($excludingTaskId, fn($q) => $q->where('id', ComparisonOperator::NotEqual->value, $excludingTaskId))
                    ->whereHas('project', fn($projectQuery) => $projectQuery->where('user_id', $userId));
            })
            ->latest('started_at')
            ->lockForUpdate()
            ->first();
    }

    public function createRunning(string $taskId): Timer
    {
        $timer = new Timer();
        $timer->task_id = $taskId;
        $timer->started_at = now();
        $timer->save();

        return $timer;
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

        $row->stopped_at = $now;

        if ($row->started_at) {
            $startedAt = Carbon::parse($row->started_at);
            $seconds = $startedAt->floatDiffInSeconds($now, false);
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
        return Timer::query()
            ->tap(fn($query) => Timer::applyFilters($filters)
                ->mergeConstraintsFrom($query->getModel()->newEloquentBuilder($query->getQuery()))
            )
            ->orderByDesc('started_at')
            ->paginate(20);
    }
}
