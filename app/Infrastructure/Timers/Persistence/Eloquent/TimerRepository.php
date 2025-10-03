<?php

namespace App\Infrastructure\Timers\Persistence\Eloquent;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
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

    public function stopActiveForUserOnTask(string $taskId, string $userId): ?Timer
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

        $row->stopped_at = now();
        $row->save();

        return $row;
    }

    public function stopActiveForTasks(array $taskIds): int
    {
        if ($taskIds === []) {
            return 0;
        }

        return Timer::query()
            ->whereIn('task_id', $taskIds)
            ->whereNull('stopped_at')
            ->update([
                'stopped_at' => now(),
            ]);
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
