<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\CompleteGoalDTO;
use App\Application\Projects\Services\ProjectLifecycleService;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class CompleteGoalService
{
    public function __construct(
        private GoalRepositoryInterface $goalRepository,
        private TaskRepositoryInterface $taskRepository,
        private TimerRepositoryInterface $timerRepository,
        private ProjectLifecycleService $projectLifecycleService,
        private TransactionRunner $transactionRunner,
    ) {}

    /**
     * @return array{goal_id:string,status:?string,completed_at:?string,cascade:array{tasks_marked_complete:int,timers_stopped:int}}
     */
    public function handle(CompleteGoalDTO $dto): array
    {
        $result = $this->transactionRunner->run(function () use ($dto) {
            $goal = $this->goalRepository->findOwned($dto->goalId, $dto->projectId, $dto->userId);

            $tasks = $this->taskRepository->getTasksByGoal($goal->id, $dto->projectId, $dto->userId);

            $incompleteTaskIds = $tasks
                ->filter(fn($task) => $task->status !== 'done')
                ->pluck('id')
                ->values()
                ->all();

            $tasksMarkedComplete = 0;

            if ($incompleteTaskIds !== []) {
                $tasksMarkedComplete = $this->taskRepository->markTasksAsComplete($incompleteTaskIds);

                if ($tasksMarkedComplete !== count($incompleteTaskIds)) {
                    Log::warning('Failed to mark goal tasks as complete', [
                        'goal_id' => $dto->goalId,
                        'project_id' => $dto->projectId,
                        'user_id' => $dto->userId,
                        'expected' => count($incompleteTaskIds),
                        'affected' => $tasksMarkedComplete,
                        'task_ids' => $incompleteTaskIds,
                    ]);

                    throw new RuntimeException('Failed to mark all goal tasks as complete.');
                }
            }

            $timersStopped = $tasks->isEmpty()
                ? 0
                : $this->timerRepository->stopActiveTimersForTasks($tasks->pluck('id')->values()->all());

            $snapshot = $goal->status === 'complete'
                ? GoalSnapshot::fromModel($goal)
                : $this->goalRepository->updateStatusSnapshot($goal->id, 'complete', now());

            return [
                'goal_id' => $snapshot->id,
                'status' => $snapshot->status,
                'completed_at' => $snapshot->completedAt,
                'cascade' => [
                    'tasks_marked_complete' => $tasksMarkedComplete,
                    'timers_stopped' => $timersStopped,
                ],
            ];
        });

        $this->projectLifecycleService->refresh($dto->projectId, $dto->userId);

        return $result;
    }
}
