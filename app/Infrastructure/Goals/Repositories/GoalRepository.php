<?php

namespace App\Infrastructure\Goals\Repositories;

use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Support\Collection;

class GoalRepository implements GoalRepositoryInterface
{
    public function create(array $data): Goal
    {
        return Goal::query()->create($data);
    }

    public function list(array $filters, Project $project): Collection
    {
        $goalIds = $project->goals()->pluck('id');

        return Goal::applyFilters($filters)
            ->whereIn('id', $goalIds)
            ->get();
    }

    public function findOwned(string $goalId, string $projectId, string $userId): Goal
    {
        return Goal::query()
            ->ownedBy($projectId, $userId)
            ->whereKey($goalId)
            ->firstOrFail();
    }

    public function findSnapshot(string $goalId, string $projectId, string $userId): GoalSnapshot
    {
        $goal = $this->findOwned($goalId, $projectId, $userId);

        return GoalSnapshot::fromModel($goal);
    }

    public function updateStatusSnapshot(string $goalId, string $status, ?string $completedAt = null): GoalSnapshot
    {
        Goal::query()
            ->where('id', $goalId)
            ->update([
                'status'       => $status,
                'completed_at' => $completedAt,
            ]);

        $goal = Goal::query()->findOrFail($goalId);

        return GoalSnapshot::fromModel($goal);
    }

    public function getByProject(string $projectId, string $userId): Collection
    {
        return Goal::query()
            ->ownedBy($projectId, $userId)
            ->get();
    }

    public function delete(Goal $goal): void
    {
        $goal->delete();
    }
}
