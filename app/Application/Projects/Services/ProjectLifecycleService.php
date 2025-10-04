<?php

namespace App\Application\Projects\Services;

use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\Enums\ProjectCompletionSource;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;

class ProjectLifecycleService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly GoalRepositoryInterface $goalRepository,
    ) {}

    public function refresh(string $projectId, string $userId): void
    {
        $project = $this->projectRepository->findOwned($projectId, $userId);

        if ($project->completion_source === ProjectCompletionSource::Manual->value) {
            return;
        }

        $totalTasks = $this->taskRepository->countByProject($projectId, $userId);
        $incompleteTasks = $this->taskRepository->countIncompleteByProject($projectId, $userId);

        $totalGoals = $this->goalRepository->countByProject($projectId, $userId);
        $incompleteGoals = $this->goalRepository->countIncompleteByProject($projectId, $userId);

        $hasWorkItems = ($totalTasks + $totalGoals) > 0;
        $hasIncompleteWork = ($incompleteTasks + $incompleteGoals) > 0;

        if ($hasWorkItems && ! $hasIncompleteWork) {
            $this->projectRepository->markComplete($project, ProjectCompletionSource::Automatic->value);
            return;
        }

        if ($hasIncompleteWork || ! $hasWorkItems) {
            $this->projectRepository->markActive($project);
        }
    }

    public function completeManually(string $projectId, string $userId): Project
    {
        $project = $this->projectRepository->findOwned($projectId, $userId);

        return $this->projectRepository->markComplete($project, ProjectCompletionSource::Manual->value);
    }
}
