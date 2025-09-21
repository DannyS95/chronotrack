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
}
