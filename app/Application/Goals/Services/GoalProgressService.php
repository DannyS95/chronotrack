<?php

namespace App\Application\Goals\Services;

use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;

final class GoalProgressService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goals,
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function handle(Project $project, Goal $goal, string $userId): array
    {
        $ownedGoal = $this->goals->findOwned($goal->id, $project->id, $userId);

        $tasks = $this->tasks->getByGoal($ownedGoal->id, $project->id, $userId);

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $percent = $totalTasks > 0
            ? (int) round(($completedTasks / $totalTasks) * 100)
            : 0;

        if ($totalTasks > 0 && $completedTasks === $totalTasks && $ownedGoal->status !== 'complete') {
            $ownedGoal = $this->goals->updateStatus($ownedGoal->id, 'complete', now());
        }

        return [
            'goal_id'          => $ownedGoal->id,
            'total_tasks'      => $totalTasks,
            'completed_tasks'  => $completedTasks,
            'percent_complete' => $percent,
            'tasks'            => $tasks->map(fn($task) => [
                'id'     => $task->id,
                'title'  => $task->title,
                'status' => $task->status,
            ])->values()->all(),
        ];
    }
}
