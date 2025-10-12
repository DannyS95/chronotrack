<?php

namespace App\Application\Goals\ViewModels;

use App\Domain\Common\Support\TimerDurations;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use Illuminate\Support\Collection;

final class GoalProgressViewModel
{
    /** @var array<int, TaskProgressViewModel> */
    private array $tasks;

    private function __construct(
        private readonly string $goalId,
        private readonly ?string $status,
        private readonly ?string $completedAt,
        private readonly int $totalTasks,
        private readonly int $completedTasks,
        private readonly int $percentComplete,
        private readonly int $accumulatedSeconds,
        private readonly ?string $accumulatedHuman,
        private readonly ?string $activeSince,
        array $tasks,
    ) {
        $this->tasks = $tasks;
    }

    /**
     * @param Collection<int, TaskSnapshot> $taskSnapshots
     */
    public static function fromSnapshots(GoalSnapshot $goal, Collection $taskSnapshots): self
    {
        $totalTasks = $taskSnapshots->count();
        $completedTasks = $taskSnapshots->filter(fn(TaskSnapshot $task) => $task->isComplete())->count();
        $percent = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $tasks = $taskSnapshots
            ->map(fn(TaskSnapshot $task) => TaskProgressViewModel::fromSnapshot($task))
            ->values()
            ->all();

        $elapsedSeconds = TimerDurations::sumDurationsFromSnapshots($taskSnapshots);

        $elapsedHuman = TimerDurations::humanizeSeconds($elapsedSeconds);

        $activeSince = TimerDurations::determineActiveSinceFromSnapshots($taskSnapshots);

        return new self(
            goalId: $goal->id,
            status: $goal->status,
            completedAt: $goal->completedAt,
            totalTasks: $totalTasks,
            completedTasks: $completedTasks,
            percentComplete: $percent,
            accumulatedSeconds: $elapsedSeconds,
            accumulatedHuman: $elapsedHuman,
            activeSince: $activeSince,
            tasks: $tasks,
        );
    }

    public function withCompletionUpdated(GoalSnapshot $goal): self
    {
        return new self(
            goalId: $goal->id,
            status: $goal->status,
            completedAt: $goal->completedAt,
            totalTasks: $this->totalTasks,
            completedTasks: $this->completedTasks,
            percentComplete: $this->percentComplete,
            accumulatedSeconds: $this->accumulatedSeconds,
            accumulatedHuman: $this->accumulatedHuman,
            activeSince: $this->activeSince,
            tasks: $this->tasks,
        );
    }

    public function withProgressForcedToHundred(): self
    {
        return new self(
            goalId: $this->goalId,
            status: $this->status,
            completedAt: $this->completedAt,
            totalTasks: $this->totalTasks,
            completedTasks: $this->completedTasks,
            percentComplete: $this->totalTasks > 0 ? 100 : $this->percentComplete,
            accumulatedSeconds: $this->accumulatedSeconds,
            accumulatedHuman: $this->accumulatedHuman,
            activeSince: $this->activeSince,
            tasks: $this->tasks,
        );
    }

    public function status(): ?string
    {
        return $this->status;
    }

    public function completedAt(): ?string
    {
        return $this->completedAt;
    }

    public function totalTasks(): int
    {
        return $this->totalTasks;
    }

    public function completedTasks(): int
    {
        return $this->completedTasks;
    }

    public function percentComplete(): int
    {
        return $this->percentComplete;
    }

    public function accumulatedSeconds(): int
    {
        return $this->accumulatedSeconds;
    }

    public function accumulatedHuman(): ?string
    {
        return $this->accumulatedHuman;
    }

    public function activeSince(): ?string
    {
        return $this->activeSince;
    }

    /**
     * @return array<int, array{id:string,title:string,status:?string,active_since:?string,accumulated_seconds:int,accumulated_human:?string}>
     */
    public function tasks(): array
    {
        return array_map(fn(TaskProgressViewModel $task) => $task->toArray(), $this->tasks);
    }

    public function toArray(): array
    {
        return [
            'goal_id'          => $this->goalId,
            'status'           => $this->status(),
            'completed_at'     => $this->completedAt(),
            'total_tasks'      => $this->totalTasks(),
            'completed_tasks'  => $this->completedTasks(),
            'percent_complete' => $this->percentComplete(),
            'active_since'     => $this->activeSince(),
            'accumulated_seconds'  => $this->accumulatedSeconds(),
            'accumulated_human'    => $this->accumulatedHuman(),
            'tasks'            => $this->tasks(),
        ];
    }
}
