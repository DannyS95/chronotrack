<?php

namespace App\Infrastructure\Timers\Persistence\Eloquent;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TimerRepository implements TimerRepositoryInterface
{
    public function findActiveForUserLock(int $userId): ?Timer
    {
        return Timer::query()
            ->select('timers.*')
            ->join('tasks', 'tasks.id', '=', 'timers.task_id')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->whereNull('timers.stopped_at')
            ->where('projects.user_id', $userId)
            ->latest('timers.started_at')
            ->lockForUpdate()
            ->first();
    }

    public function createRunning(string $taskId, int $userId): Timer
    {
        $timer = new Timer();
        $timer->task_id = $taskId;
        $timer->started_at = now();
        $timer->save();

        return $timer;
    }

    public function stopActiveForUserOnTask(string $taskId, int $userId): ?Timer
    {
        $row = Timer::query()
            ->join('tasks', 'tasks.id', '=', 'timers.task_id')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->where('timers.task_id', $taskId)
            ->where('projects.user_id', $userId)
            ->whereNull('timers.stopped_at')
            ->lockForUpdate()
            ->first(['timers.*']);

        if (! $row) {
            return null;
        }

        $row->stopped_at = now();
        $row->save();

        return $row;
    }

    public function findActiveWithContext(int $userId): ?Timer
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
            ->tap(fn($query) => Timer::filters($filters)
                ->mergeConstraintsFrom($query->getModel()->newEloquentBuilder($query->getQuery()))
            )
            ->orderByDesc('started_at')
            ->paginate(20);
    }
}
