<?php

namespace App\Infrastructure\Goals\Repositories;

use App\Domain\Goals\Contracts\GoalRepositoryInterface;
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
            ->select('goals.*')
            ->join('projects', 'goals.project_id', '=', 'projects.id')
            ->where('goals.id', $goalId)
            ->where('goals.project_id', $projectId)
            ->where('projects.user_id', $userId)
            ->firstOrFail();
    }
}
