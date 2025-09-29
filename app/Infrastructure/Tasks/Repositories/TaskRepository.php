<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function getFiltered(array $filters, string $user_id, string $project_id): Builder
    {
        // Ensure the project exists and belongs to the user
        $project = Project::query()
            ->where('id', $project_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        return Task::applyFilters($filters)
            ->where('project_id', $project->id)
            ->whereHas('project', fn($q) => $q->where('user_id', $user_id));
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
            ->with(['tasks' => fn($q) => $q->select('id', 'title', 'status', 'goal_id', 'project_id')->orderBy('created_at')])
            ->firstOrFail();

        return $goal->tasks
            ->map(fn(Task $task) => TaskSnapshot::fromModel($task))
            ->values();
    }
}
