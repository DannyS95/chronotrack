<?php

namespace App\Application\Goals\Services;

use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;

final class ShowGoalService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goalRepository
    ) {}

    public function handle(Project $project, Goal $goal, int|string $userId): array
    {
        if ($goal->project_id !== $project->id) {
            throw new AuthorizationException('Goal does not belong to the provided project.');
        }

        $ownedGoal = $this->goalRepository->findOwned($goal->id, $project->id, (string) $userId);

        return Arr::only($ownedGoal->toArray(), [
            'id',
            'title',
            'description',
            'status',
            'deadline',
            'created_at',
            'updated_at',
        ]);
    }
}
