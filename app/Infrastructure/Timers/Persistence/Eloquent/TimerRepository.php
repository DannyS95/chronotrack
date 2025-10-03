<?php

namespace App\Infrastructure\Timers\Persistence\Eloquent;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TimerRepository implements TimerRepositoryInterface
{
    public function findActiveForUserLock(string $task_id): ?Timer
    {
        return Timer::query()
            ->where('task_id', $task_id)
            ->whereNull('stopped_at')
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
        $row->duration = $row->started_at
            ? $now->diffInSeconds(Carbon::parse($row->started_at))
            : $row->duration;
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
            $timer->duration = $timer->started_at
                ? $now->diffInSeconds(Carbon::parse($timer->started_at))
                : $timer->duration;
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
