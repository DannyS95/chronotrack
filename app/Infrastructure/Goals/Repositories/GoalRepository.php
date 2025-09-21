<?php

namespace App\Infrastructure\Goals\Repositories;

use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use Illuminate\Support\Collection;

class GoalRepository implements GoalRepositoryInterface
{
    public function create(array $data): Goal
    {
        return Goal::query()->create($data);
    }

    public function list(array $filters): Collection
    {
        return Goal::applyFilters($filters)->get();
    }
}
