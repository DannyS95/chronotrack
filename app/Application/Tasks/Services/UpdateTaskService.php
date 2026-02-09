<?php

namespace App\Application\Tasks\Services;

use App\Application\Tasks\DTO\UpdateTaskDTO;
use App\Application\Tasks\ViewModels\TaskViewModel;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class UpdateTaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private GoalRepositoryInterface $goalRepository,
        private TimerRepositoryInterface $timerRepository,
        private TransactionRunner $tx,
    ) {}

    public function handle(UpdateTaskDTO $dto): TaskViewModel
    {
        if ($dto->attributes === []) {
            throw ValidationException::withMessages([
                'data' => ['No changes provided for update.'],
            ]);
        }

        $snapshot = $this->tx->run(function () use ($dto) {
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

            $shouldCompleteTask = array_key_exists('status', $dto->attributes)
                && $dto->attributes['status'] === 'done'
                && $task->status !== 'done';

            $attributes = $dto->toArray();

            if ($shouldCompleteTask) {
                $now = Carbon::now();
                $attributes['last_activity_at'] = $attributes['last_activity_at'] ?? $now;

                $hasTimers = $task->timers()->exists();

                if (! $hasTimers && ($task->time_spent_seconds === null || $task->time_spent_seconds === 0) && $task->created_at) {
                    $attributes['time_spent_seconds'] = max(1, $now->diffInSeconds($task->created_at));
                }
            }

            $snapshot = $this->taskRepository->updateSnapshot($task, $attributes);

            if ($shouldCompleteTask) {
                $this->timerRepository->stopActiveTimerForTask($task->id);

                if ($task->goal_id !== null) {
                    $remaining = $this->taskRepository->countIncompleteByGoal(
                        $task->goal_id,
                        $dto->project_id,
                        $dto->user_id,
                    );

                    if ($remaining === 0) {
                        $this->goalRepository->updateStatusSnapshot($task->goal_id, 'complete', now());
                    }
                }

                $snapshot = $this->taskRepository->findSnapshot(
                    $task->id,
                    $dto->project_id,
                    $dto->user_id,
                );
            }

            return $snapshot;
        });

        return TaskViewModel::fromSnapshot($snapshot);
    }
}
