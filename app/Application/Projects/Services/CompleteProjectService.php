<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\DTO\CompleteProjectDTO;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerOperationBlocked;

final class CompleteProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TimerRepositoryInterface $timerRepository,
        private readonly ProjectLifecycleService $projectLifecycleService,
    ) {}

    /**
     * @return array{project_id:string,status:string,timers_stopped:int}
     */
    public function handle(CompleteProjectDTO $dto): array
    {
        $project = $this->projectRepository->findOwned($dto->projectId, $dto->userId);

        $runningTimers = $this->timerRepository->countActiveByProject($project->id, $dto->userId);

        if ($runningTimers > 0) {
            throw new ActiveTimerOperationBlocked('project', $runningTimers);
        }

        $updatedProject = $this->projectLifecycleService->completeManually($project->id, $dto->userId);

        return [
            'project_id' => $updatedProject->id,
            'status' => $updatedProject->status,
            'timers_stopped' => 0,
        ];
    }
}
