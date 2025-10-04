<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class DetachTaskFromGoalService
{
    public function __construct(
        private GoalRepositoryInterface $goalRepository,
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function handle(AttachTaskToGoalDTO $dto): void
    {
        // Verify goal ownership
        $goal = $this->goalRepository->findOwned($dto->goalId, $dto->projectId, $dto->userId);

        if ($goal->status === 'complete') {
            throw ValidationException::withMessages([
                'goal_id' => ['Cannot detach tasks from a completed goal.'],
            ]);
        }

        // Verify task ownership
        $task = $this->taskRepository->findOwned($dto->taskId, $dto->projectId, $dto->userId);

        if ($task->goal_id !== $goal->id) {
            throw new AuthorizationException('Task is not attached to the provided goal.');
        }

        // Detach by clearing task's goal reference
        $this->taskRepository->updateGoal($task, null);
    }
}
