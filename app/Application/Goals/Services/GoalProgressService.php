<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\ViewModels\GoalProgressViewModel;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

final class GoalProgressService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goalRepository,
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(string $projectId, string $goalId, string $userId): GoalProgressViewModel
    {
        $goalSnapshot = $this->goalRepository->findSnapshot($goalId, $projectId, $userId);
        $taskSnapshots = $this->taskRepository->getSnapshotsByGoal($goalSnapshot->id, $projectId, $userId);

        $viewModel = GoalProgressViewModel::fromSnapshots($goalSnapshot, $taskSnapshots);

        $allComplete = $viewModel->totalTasks() > 0
            && $viewModel->completedTasks() === $viewModel->totalTasks();

        if ($allComplete && ! $goalSnapshot->isComplete()) {
            $goalSnapshot = $this->goalRepository->updateStatusSnapshot($goalSnapshot->id, 'complete', now());
            $viewModel = $viewModel->withCompletionUpdated($goalSnapshot);
        }

        return $viewModel;
    }
}
