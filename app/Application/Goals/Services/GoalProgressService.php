<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\ViewModels\GoalProgressViewModel;
use App\Application\Projects\Services\ProjectLifecycleService;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class GoalProgressService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goalRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectLifecycleService $projectLifecycleService,
    ) {}

    public function handle(string $projectId, string $goalId, string $userId): GoalProgressViewModel
    {
        $goalSnapshot = $this->goalRepository->findSnapshot($goalId, $projectId, $userId);
        $taskSnapshots = $this->taskRepository->getSnapshotsByGoal($goalSnapshot->id, $projectId, $userId);

        $viewModel = GoalProgressViewModel::fromSnapshots($goalSnapshot, $taskSnapshots);

        if ($viewModel->totalTasks() > 0 && $viewModel->completedTasks() === $viewModel->totalTasks()) {
            return $viewModel->withProgressForcedToHundred();
        }

        return $viewModel;
    }
}
