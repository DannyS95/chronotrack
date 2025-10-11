<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\ViewModels\ProjectSummaryViewModel;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;

final class ProjectSummaryService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly GoalRepositoryInterface $goalRepository,
        private readonly TimerRepositoryInterface $timerRepository,
    ) {}

    public function handle(string $projectId, string $userId): ProjectSummaryViewModel
    {
        $project = $this->projectRepository->findOwned($projectId, $userId);

        $taskSnapshots = $this->taskRepository
            ->getTasksByProject($projectId, $userId)
            ->map(fn($task) => TaskSnapshot::fromModel($task))
            ->values();

        $goalSnapshots = $this->goalRepository
            ->getByProject($projectId, $userId)
            ->map(fn($goal) => GoalSnapshot::fromModel($goal))
            ->values();

        $runningTimers = $this->timerRepository->countActiveByProject($projectId, $userId);

        return ProjectSummaryViewModel::fromCollections(
            $project,
            $taskSnapshots,
            $goalSnapshots,
            $runningTimers
        );
    }
}
