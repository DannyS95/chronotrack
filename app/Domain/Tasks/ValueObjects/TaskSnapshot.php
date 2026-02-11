<?php

namespace App\Domain\Tasks\ValueObjects;

use App\Domain\Common\Support\TimerDurations;
use App\Infrastructure\Tasks\Eloquent\Models\Task as TaskModel;
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
        public readonly string $timerType,
        public readonly ?int $targetDurationSeconds,
        public readonly ?string $targetDurationHuman,
        public readonly ?int $progressPercent,
        public readonly ?string $dueAt,
        public readonly ?string $lastActivityAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $activeSince,
        public readonly int $accumulatedSeconds,
        public readonly ?string $accumulatedHuman,
        public readonly bool $hasActiveTimers,
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

        $trackedSeconds = max(0, self::calculateTimerSeconds($timers));
        $activeSinceDate = TimerDurations::determineActiveSinceFromCollection($timers);
        $activeSince = $activeSinceDate ? $formatDate($activeSinceDate) : null;
        $hasActiveTimers = $timers->contains(fn(Timer $timer) => $timer->stopped_at === null);

        $storedSeconds = (int) ($task->time_spent_seconds ?? 0);
        $fallbackSeconds = self::calculateFallbackSeconds($task);
        $accumulatedSeconds = $trackedSeconds > 0
            ? $trackedSeconds
            : ($storedSeconds > 0 ? $storedSeconds : $fallbackSeconds);
        $accumulatedHuman = $accumulatedSeconds > 0 ? TimerDurations::humanizeSeconds($accumulatedSeconds) : null;

        $timerType = (string) ($task->timer_type ?? 'custom');
        $targetDurationSeconds = $task->target_duration_seconds !== null
            ? max(0, (int) $task->target_duration_seconds)
            : null;
        $targetDurationHuman = $targetDurationSeconds !== null && $targetDurationSeconds > 0
            ? TimerDurations::humanizeSeconds($targetDurationSeconds)
            : null;
        $progressPercent = $targetDurationSeconds !== null && $targetDurationSeconds > 0
            ? min(100, (int) round(($accumulatedSeconds / $targetDurationSeconds) * 100))
            : null;

        return new self(
            id: $task->id,
            projectId: $task->project_id,
            goalId: $task->goal_id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            timerType: $timerType,
            targetDurationSeconds: $targetDurationSeconds,
            targetDurationHuman: $targetDurationHuman,
            progressPercent: $progressPercent,
            dueAt: $formatDate($task->due_at),
            lastActivityAt: $formatDate(self::determineLastActivity($task, $timers)),
            createdAt: $formatDate($task->created_at),
            updatedAt: $formatDate($task->updated_at),
            activeSince: $activeSince,
            accumulatedSeconds: $accumulatedSeconds,
            accumulatedHuman: $accumulatedHuman,
            hasActiveTimers: $hasActiveTimers,
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
     * @param iterable<int, Timer> $timers
     */
    private static function calculateTimerSeconds(iterable $timers): int
    {
        return TimerDurations::sumDurationsFromCollection($timers, new \DateTimeImmutable());
    }

    private static function calculateFallbackSeconds(TaskModel $task): int
    {
        if ($task->status !== 'done') {
            return 0;
        }

        if (! $task->created_at) {
            return 0;
        }

        $start = self::parseDateTime($task->created_at);
        if ($start === null) {
            return 0;
        }

        $endCandidate = $task->last_activity_at
            ?? $task->updated_at
            ?? null;

        if ($endCandidate === null) {
            return 0;
        }

        $end = self::parseDateTime($endCandidate);
        if ($end === null) {
            return 0;
        }

        return max(0, $end->getTimestamp() - $start->getTimestamp());
    }

    /**
     * Determine the best "last activity" timestamp for the task.
     *
     * We prioritise the moment the last timer stopped, because that is the most
     * explicit signal of recent work. Otherwise we fall back to the explicit
     * last_activity_at column, then updated_at, and finally creation time.
     */
    private static function determineLastActivity(TaskModel $task, iterable $timers): ?string
    {
        $lastStoppedAt = null;

        foreach ($timers as $timer) {
            if ($timer->stopped_at === null) {
                continue;
            }

            $stoppedAt = self::parseDateTime($timer->stopped_at);
            if ($stoppedAt === null) {
                continue;
            }

            if ($lastStoppedAt === null || $stoppedAt > $lastStoppedAt) {
                $lastStoppedAt = $stoppedAt;
            }
        }

        if ($lastStoppedAt !== null) {
            return $lastStoppedAt->format('Y-m-d H:i:s');
        }

        $source = $task->last_activity_at
            ?? $task->updated_at
            ?? $task->created_at;

        return self::parseDateTime($source)?->format('Y-m-d H:i:s');
    }

    private static function parseDateTime(mixed $value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (is_string($value) && $value !== '') {
            try {
                return new \DateTimeImmutable($value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }
}
