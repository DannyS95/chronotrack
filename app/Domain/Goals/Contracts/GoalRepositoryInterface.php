<?php

namespace App\Domain\Goals\Contracts;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Support\Collection;

interface GoalRepositoryInterface
{
    public function create(array $data): Goal;

    public function list(array $filters, Project $project): Collection;

    public function findOwned(string $goalId, string $projectId, string $userId): Goal;

    public function updateStatus(string $goalId, string $status, ?string $completedAt = null): Goal;
}
