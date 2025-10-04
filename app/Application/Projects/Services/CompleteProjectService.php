<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\DTO\CompleteProjectDTO;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;

final class CompleteProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TimerRepositoryInterface $timerRepository,
        private readonly ProjectLifecycleService $projectLifecycleService,
    ) {}

    /**
     * @return array{project_id:string,status:string,timers_stopped:int}
     */
    public function handle(CompleteProjectDTO $dto): array
    {
        $project = $this->projectRepository->findOwned($dto->projectId, $dto->userId);

        $tasks = $this->taskRepository->getTasksByProject($project->id, $dto->userId);
        $taskIds = $tasks->pluck('id')->values()->all();

        $timersStopped = $taskIds === []
            ? 0
            : $this->timerRepository->stopActiveTimersForTasks($taskIds);

        $updatedProject = $this->projectLifecycleService->completeManually($project->id, $dto->userId);

        return [
            'project_id' => $updatedProject->id,
            'status' => $updatedProject->status,
            'timers_stopped' => $timersStopped,
        ];
    }
}
