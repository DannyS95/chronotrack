<?php

namespace App\Domain\Tasks\ValueObjects;

use App\Domain\Common\Support\TimerDurations;
use App\Infrastructure\Tasks\Eloquent\Models\Task as TaskModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

final class TaskSnapshot
{
    public function __construct(
        public readonly string $id,
        public readonly string $projectId,
        public readonly ?string $goalId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $status,
        public readonly ?string $dueAt,
        public readonly ?string $lastActivityAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $activeSince,
        public readonly int $activeDurationSeconds,
        public readonly ?string $activeDurationHuman,
        public readonly bool $hasActiveTimers,
        public readonly int $timeSpentSeconds,
        public readonly ?string $timeSpentHuman,
    ) {}

    public static function fromModel(TaskModel $task): self
    {
        $formatDate = static fn($value) => match (true) {
            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_string($value)                    => $value,
            default                              => null,
        };

        $timers = $task->relationLoaded('timers')
            ? $task->timers
            : $task->timers()->get();

        $totalSeconds = max(0, self::calculateTimerSeconds($timers));
        $activeSinceDate = TimerDurations::determineActiveSinceFromCollection($timers);
        $activeSince = $activeSinceDate ? $formatDate($activeSinceDate) : null;
        $activeDurationHuman = TimerDurations::humanizeSeconds($totalSeconds);
        $hasActiveTimers = $timers->contains(fn(Timer $timer) => $timer->stopped_at === null);

        $storedSeconds = (int) ($task->time_spent_seconds ?? 0);
        $fallbackSeconds = self::calculateFallbackSeconds($task);
        $timeSpentSeconds = $totalSeconds > 0
            ? $totalSeconds
            : ($storedSeconds > 0 ? $storedSeconds : $fallbackSeconds);
        $timeSpentHuman = $timeSpentSeconds > 0 ? TimerDurations::humanizeSeconds($timeSpentSeconds) : null;

        return new self(
            id: $task->id,
            projectId: $task->project_id,
            goalId: $task->goal_id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            dueAt: $formatDate($task->due_at),
            lastActivityAt: $formatDate(self::determineLastActivity($task, $timers)),
            createdAt: $formatDate($task->created_at),
            updatedAt: $formatDate($task->updated_at),
            activeSince: $activeSince,
            activeDurationSeconds: $totalSeconds,
            activeDurationHuman: $activeDurationHuman,
            hasActiveTimers: $hasActiveTimers,
            timeSpentSeconds: $timeSpentSeconds,
            timeSpentHuman: $timeSpentHuman,
        );
    }

    public function isComplete(): bool
    {
        return $this->status === 'done';
    }

    public function hasActiveTimers(): bool
    {
        return $this->hasActiveTimers;
    }

    /**
     * @param Collection<int, Timer> $timers
     */
    private static function calculateTimerSeconds(Collection $timers): int
    {
        if ($timers->isEmpty()) {
            return 0;
        }

        $now = Carbon::now();

        # walk through timers and sum the total duration or calculate the running time from diff in seconds
        return TimerDurations::sumDurationsFromCollection($timers, $now);
    }

    private static function calculateFallbackSeconds(TaskModel $task): int
    {
        if ($task->status !== 'done') {
            return 0;
        }

        if (! $task->created_at) {
            return 0;
        }

        $start = Carbon::parse($task->created_at);

        $endCandidate = $task->last_activity_at
            ?? $task->updated_at
            ?? null;

        if ($endCandidate === null) {
            return 0;
        }

        $end = Carbon::parse($endCandidate);

        return max(0, $end->diffInSeconds($start));
    }

    /**
     * Determine the best "last activity" timestamp for the task.
     *
     * We prioritise the moment the last timer stopped, because that is the most
     * explicit signal of recent work. Otherwise we fall back to the explicit
     * last_activity_at column, then updated_at, and finally creation time.
     */
    private static function determineLastActivity(TaskModel $task, Collection $timers): ?string
    {
        $lastTimer = $timers->filter(fn(Timer $timer) => $timer->stopped_at !== null)
            ->sortByDesc(fn(Timer $timer) => $timer->stopped_at)
            ->first();

        if ($lastTimer) {
            return Carbon::parse($lastTimer->stopped_at)->format('Y-m-d H:i:s');
        }

        $source = $task->last_activity_at
            ?? $task->updated_at
            ?? $task->created_at;

        return $source ? Carbon::parse($source)->format('Y-m-d H:i:s') : null;
    }
}
