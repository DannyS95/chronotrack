<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;

class DetachTaskFromGoalService
{
    public function __construct(
        private GoalRepositoryInterface $goals,
        private TaskRepositoryInterface $tasks
    ) {}

    public function handle(AttachTaskToGoalDTO $dto): void
    {
        // Verify goal ownership
        $goal = $this->goals->findOwned($dto->goalId, $dto->projectId, $dto->userId);

        // Verify task ownership
        $task = $this->tasks->findOwned($dto->taskId, $dto->projectId, $dto->userId);

        // Detach
        $this->goals->detachTask($goal->id, $task->id);
    }
}
