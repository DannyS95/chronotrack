<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AttachTaskToGoalService
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
                'goal_id' => ['Cannot attach tasks to a completed goal.'],
            ]);
        }

        // Verify task ownership
        $task = $this->taskRepository->findOwned($dto->taskId, $dto->projectId, $dto->userId);

        if ($task->status === 'done') {
            throw ValidationException::withMessages([
                'task_id' => ['Cannot attach a completed task to a goal.'],
            ]);
        }

        if ($task->goal_id === $goal->id) {
            throw ValidationException::withMessages([
                'task_id' => ['Task is already attached to this goal.'],
            ]);
        }

        // Attach by setting task's goal reference
        $this->taskRepository->updateGoal($task, $goal->id);
    }
}
