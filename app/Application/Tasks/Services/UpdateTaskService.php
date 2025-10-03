<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\UpdateTaskDTO;
use App\Application\Tasks\ViewModels\TaskViewModel;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use Illuminate\Validation\ValidationException;

final class UpdateTaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private GoalRepositoryInterface $goalRepository,
    ) {}

    public function handle(UpdateTaskDTO $dto): TaskViewModel
    {
        if ($dto->attributes === []) {
            throw ValidationException::withMessages([
                'data' => ['No changes provided for update.'],
            ]);
        }

        $task = $this->taskRepository->findOwned(
            $dto->task_id,
            $dto->project_id,
            $dto->user_id,
        );

        if (array_key_exists('goal_id', $dto->attributes)) {
            $this->goalRepository->findOwned(
                $dto->goal_id,
                $dto->project_id,
                $dto->user_id,
            );
        }

        $snapshot = $this->taskRepository->updateSnapshot($task, $dto->toArray());

        return TaskViewModel::fromSnapshot($snapshot);
    }
}
