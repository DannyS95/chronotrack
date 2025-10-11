<?php

namespace App\Application\Projects\ViewModels;

use App\Domain\Common\Support\TimerDurations;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Support\Collection;

final class ProjectSummaryViewModel
{
    /**
     * @param array<string, mixed> $project
     * @param array<string, mixed> $tasks
     * @param array<string, mixed> $goals
     * @param array<string, mixed> $timers
     */
    private function __construct(
        private readonly array $project,
        private readonly array $tasks,
        private readonly array $goals,
        private readonly array $timers,
    ) {}

    /**
     * @param Collection<int, TaskSnapshot> $taskSnapshots
     * @param Collection<int, GoalSnapshot> $goalSnapshots
     */
    public static function fromCollections(
        Project $project,
        Collection $taskSnapshots,
        Collection $goalSnapshots,
        int $runningTimers,
    ): self {
        $projectData = self::projectToArray($project);

        $taskStats = self::buildTaskStats($taskSnapshots);
        $goalStats = self::buildGoalStats($goalSnapshots, $taskSnapshots);
        $timerStats = self::buildTimerStats($taskSnapshots, $runningTimers);

        return new self(
            project: $projectData,
            tasks: $taskStats,
            goals: $goalStats,
            timers: $timerStats,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'project' => $this->project,
            'tasks' => $this->tasks,
            'goals' => $this->goals,
            'timers' => $this->timers,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function projectToArray(Project $project): array
    {
        $formatDateOnly = static fn($value) => match (true) {
            $value instanceof \DateTimeInterface => $value->format('Y-m-d'),
            is_string($value) => $value,
            default => null,
        };

        $formatDateTime = static fn($value) => match (true) {
            $value instanceof \DateTimeInterface => $value->format('c'),
            is_string($value) => $value,
            default => null,
        };

        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'deadline' => $formatDateOnly($project->deadline),
            'user_id' => $project->user_id,
            'status' => $project->status,
            'completed_at' => $formatDateTime($project->completed_at),
            'completion_source' => $project->completion_source,
            'created_at' => $formatDateTime($project->created_at),
            'updated_at' => $formatDateTime($project->updated_at),
        ];
    }

    /**
     * @param Collection<int, TaskSnapshot> $taskSnapshots
     * @return array<string, mixed>
     */
    private static function buildTaskStats(Collection $taskSnapshots): array
    {
        $total = $taskSnapshots->count();
        $completed = $taskSnapshots
            ->filter(fn(TaskSnapshot $task) => $task->isComplete())
            ->count();
        $active = $total - $completed;
        $runningCount = $taskSnapshots
            ->filter(fn(TaskSnapshot $task) => $task->hasActiveTimers())
            ->count();

        $elapsedSeconds = TimerDurations::sumDurationsFromSnapshots($taskSnapshots);
        $elapsedHuman = TimerDurations::humanizeSeconds($elapsedSeconds);
        $activeSince = TimerDurations::determineActiveSinceFromSnapshots($taskSnapshots);

        $timeSpentSeconds = $taskSnapshots->reduce(
            fn(int $carry, TaskSnapshot $task) => $carry + max(0, $task->timeSpentSeconds),
            0
        );
        $timeSpentHuman = TimerDurations::humanizeSeconds($timeSpentSeconds);

        $percentComplete = $total > 0
            ? (int) round(($completed / $total) * 100)
            : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'active' => $active,
            'percent_complete' => $percentComplete,
            'active_timers' => $runningCount,
            'has_running_timers' => $runningCount > 0,
            'active_since' => $activeSince,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_human' => $elapsedHuman,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_human' => $timeSpentHuman,
        ];
    }

    /**
     * @param Collection<int, GoalSnapshot> $goalSnapshots
     * @param Collection<int, TaskSnapshot> $taskSnapshots
     * @return array<string, mixed>
     */
    private static function buildGoalStats(Collection $goalSnapshots, Collection $taskSnapshots): array
    {
        $total = $goalSnapshots->count();
        $completed = $goalSnapshots
            ->filter(fn(GoalSnapshot $goal) => $goal->isComplete())
            ->count();
        $active = $total - $completed;

        $goalTaskSnapshots = $taskSnapshots
            ->filter(fn(TaskSnapshot $task) => $task->goalId !== null)
            ->values();

        $runningCount = $goalTaskSnapshots
            ->filter(fn(TaskSnapshot $task) => $task->hasActiveTimers())
            ->count();

        $elapsedSeconds = TimerDurations::sumDurationsFromSnapshots($goalTaskSnapshots);
        $elapsedHuman = TimerDurations::humanizeSeconds($elapsedSeconds);
        $activeSince = TimerDurations::determineActiveSinceFromSnapshots($goalTaskSnapshots);

        $timeSpentSeconds = $goalTaskSnapshots->reduce(
            fn(int $carry, TaskSnapshot $task) => $carry + max(0, $task->timeSpentSeconds),
            0
        );
        $timeSpentHuman = TimerDurations::humanizeSeconds($timeSpentSeconds);

        $percentComplete = $total > 0
            ? (int) round(($completed / $total) * 100)
            : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'active' => $active,
            'percent_complete' => $percentComplete,
            'active_timers' => $runningCount,
            'has_running_timers' => $runningCount > 0,
            'active_since' => $activeSince,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_human' => $elapsedHuman,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_human' => $timeSpentHuman,
        ];
    }

    /**
     * @param Collection<int, TaskSnapshot> $taskSnapshots
     * @return array<string, mixed>
     */
    private static function buildTimerStats(Collection $taskSnapshots, int $runningTimers): array
    {
        $elapsedSeconds = TimerDurations::sumDurationsFromSnapshots($taskSnapshots);
        $elapsedHuman = TimerDurations::humanizeSeconds($elapsedSeconds);
        $activeSince = TimerDurations::determineActiveSinceFromSnapshots($taskSnapshots);

        $timeSpentSeconds = $taskSnapshots->reduce(
            fn(int $carry, TaskSnapshot $task) => $carry + max(0, $task->timeSpentSeconds),
            0
        );
        $timeSpentHuman = TimerDurations::humanizeSeconds($timeSpentSeconds);

        return [
            'running' => $runningTimers,
            'has_running' => $runningTimers > 0,
            'active_since' => $activeSince,
            'tracked_seconds' => $elapsedSeconds,
            'tracked_human' => $elapsedHuman,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_human' => $timeSpentHuman,
        ];
    }
}
