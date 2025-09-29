<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

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

        if ($task->goal_id !== $goal->id) {
            throw new AuthorizationException('Task is not attached to the provided goal.');
        }

        // Detach by clearing task's goal reference
        $this->tasks->updateGoal($task, null);
    }
}
