<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AttachTaskToGoalService
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

        if ($task->goal_id === $goal->id) {
            throw ValidationException::withMessages([
                'task_id' => ['Task is already attached to this goal.'],
            ]);
        }

        // Attach by setting task's goal reference
        $this->tasks->updateGoal($task, $goal->id);
    }
}
