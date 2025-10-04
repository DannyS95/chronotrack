<?php

namespace App\Domain\Goals\Contracts;

use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Support\Collection;

interface GoalRepositoryInterface
{
    public function create(array $data): Goal;

    public function list(array $filters, Project $project): Collection;

    public function findOwned(string $goalId, string $projectId, string $userId): Goal;

    public function findSnapshot(string $goalId, string $projectId, string $userId): GoalSnapshot;

    public function updateStatusSnapshot(string $goalId, string $status, ?string $completedAt = null): GoalSnapshot;

    /** @return Collection<int, Goal> */
    public function getByProject(string $projectId, string $userId): Collection;

    public function delete(Goal $goal): void;

    public function countByProject(string $projectId, string $userId): int;

    public function countIncompleteByProject(string $projectId, string $userId): int;
}
