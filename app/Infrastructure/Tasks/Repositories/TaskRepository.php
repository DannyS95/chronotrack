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

        return Task::applyFilters($filters)
            ->ownedBy($project->id, $userId)
            ->paginate($perPage)
            ->through(fn(Task $task) => TaskSnapshot::fromModel($task));
    }

    public function userOwnsTask(string $taskId, string $userId): bool
    {
        return Task::query()
            ->ownedByUser($userId)
            ->whereKey($taskId)
            ->exists();
    }

    public function findOwned(string $taskId, string $projectId, string $userId): Task
    {
        return Task::query()
            ->ownedBy($projectId, $userId)
            ->whereKey($taskId)
            ->firstOrFail();
    }

    public function updateGoal(Task $task, ?string $goalId): void
    {
        $task->goal_id = $goalId;
        $task->save();
    }

    public function updateSnapshot(Task $task, array $attributes): TaskSnapshot
    {
        $task->fill($attributes);
        $task->save();

        return TaskSnapshot::fromModel($task->fresh());
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function getSnapshotsByGoal(string $goalId, string $projectId, string $userId): Collection
    {
        $goal = Goal::query()
            ->ownedBy($projectId, $userId)
            ->whereKey($goalId)
            ->with(['tasks' => fn($q) => $q->select('id', 'title', 'description', 'status', 'goal_id', 'project_id', 'due_at', 'last_activity_at', 'created_at', 'updated_at')->orderBy('created_at')])
            ->firstOrFail();

        return $goal->tasks
            ->map(fn(Task $task) => TaskSnapshot::fromModel($task))
            ->values();
    }

    public function getTasksByGoal(string $goalId, string $projectId, string $userId): Collection
    {
        return Task::query()
            ->ownedBy($projectId, $userId)
            ->where('goal_id', $goalId)
            ->get();
    }

    public function markTasksAsComplete(array $taskIds): int
    {
        if ($taskIds === []) {
            return 0;
        }

        return Task::query()
            ->whereKey($taskIds)
            ->update([
                'status' => 'done',
                'last_activity_at' => now(),
            ]);
    }
}
