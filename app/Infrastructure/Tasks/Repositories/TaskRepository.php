<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class TaskRepository implements TaskRepositoryInterface
{
    public function createSnapshot(array $data): TaskSnapshot
    {
        $task = Task::create($data);

        return TaskSnapshot::fromModel($task->fresh());
    }

    public function paginateSnapshots(array $filters, string $userId, string $projectId, int $perPage): LengthAwarePaginator
    {
        $project = Project::query()
            ->where('id', $projectId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $paginator = Task::applyFilters($filters)
            ->where('project_id', $project->id)
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->paginate($perPage);

        $paginator->getCollection()->transform(fn(Task $task) => TaskSnapshot::fromModel($task));

        return $paginator;
    }

    public function userOwnsTask(string $taskId, string $userId): bool
    {
        return Task::query()
            ->where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }

    public function findOwned(string $taskId, string $projectId, string $userId): Task
    {
        return Task::query()
            ->select('tasks.*')
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->where('tasks.id', $taskId)
            ->where('tasks.project_id', $projectId)
            ->where('projects.user_id', $userId)
            ->firstOrFail();
    }

    public function updateGoal(Task $task, ?string $goalId): void
    {
        $task->goal_id = $goalId;
        $task->save();
    }

    public function getSnapshotsByGoal(string $goalId, string $projectId, string $userId): Collection
    {
        $goal = Goal::query()
            ->where('id', $goalId)
            ->where('project_id', $projectId)
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->with(['tasks' => fn($q) => $q->select('id', 'title', 'description', 'status', 'goal_id', 'project_id', 'due_at', 'last_activity_at', 'created_at', 'updated_at')->orderBy('created_at')])
            ->firstOrFail();

        return $goal->tasks
            ->map(fn(Task $task) => TaskSnapshot::fromModel($task))
            ->values();
    }
}
