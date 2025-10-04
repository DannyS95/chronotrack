<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\DTO\ArchiveProjectDTO;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use Illuminate\Support\Collection;

final class ArchiveProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly GoalRepositoryInterface $goalRepository,
        private readonly TimerRepositoryInterface $timerRepository,
        private readonly TransactionRunner $tx,
    ) {}

    /**
     * @return array{project_id:string,tasks_archived:int,goals_archived:int,timers_stopped:int}
     */
    public function handle(ArchiveProjectDTO $dto): array
    {
        return $this->tx->run(function () use ($dto) {
            $project = $this->projectRepository->findOwned($dto->projectId, $dto->userId);

            $tasks = $this->taskRepository->getTasksByProject($project->id, $dto->userId);
            $taskIds = $tasks->pluck('id')->values()->all();

            $timersStopped = $taskIds === []
                ? 0
                : $this->timerRepository->stopActiveTimersForTasks($taskIds);

            if ($taskIds !== []) {
                $this->taskRepository->markTasksAsComplete($taskIds);
                $this->timerRepository->deleteTimersForTasks($taskIds);
            }

            $tasks->each(function ($task) {
                $this->taskRepository->delete($task);
            });

            $goals = $this->goalRepository->getByProject($project->id, $dto->userId);

            $goalsArchived = $this->archiveGoals($goals);

            $this->projectRepository->delete($project);

            return [
                'project_id' => $project->id,
                'tasks_archived' => $tasks->count(),
                'goals_archived' => $goalsArchived,
                'timers_stopped' => $timersStopped,
            ];
        });
    }

    private function archiveGoals(Collection $goals): int
    {
        $archived = 0;

        $goals->each(function ($goal) use (&$archived) {
            if ($goal->status !== 'complete') {
                $this->goalRepository->updateStatusSnapshot($goal->id, 'complete', now());
            }

            $this->goalRepository->delete($goal);
            $archived++;
        });

        return $archived;
    }
}
