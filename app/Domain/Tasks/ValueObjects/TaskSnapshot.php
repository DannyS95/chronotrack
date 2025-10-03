<?php

namespace App\Domain\Tasks\ValueObjects;

use App\Infrastructure\Tasks\Eloquent\Models\Task as TaskModel;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

        $totalSeconds = self::calculateTotalSeconds($timers);
        $activeSince = $formatDate($task->created_at);
        $activeDurationHuman = $totalSeconds > 0
            ? CarbonInterval::seconds($totalSeconds)->cascade()->forHumans(short: true)
            : null;

        return new self(
            id: $task->id,
            projectId: $task->project_id,
            goalId: $task->goal_id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            dueAt: $formatDate($task->due_at),
            lastActivityAt: $formatDate($task->last_activity_at),
            createdAt: $formatDate($task->created_at),
            updatedAt: $formatDate($task->updated_at),
            activeSince: $activeSince,
            activeDurationSeconds: $totalSeconds,
            activeDurationHuman: $activeDurationHuman,
        );
    }

    public function isComplete(): bool
    {
        return $this->status === 'done';
    }

    /**
     * @param Collection<int, Timer> $timers
     */
    private static function calculateTotalSeconds(Collection $timers): int
    {
        if ($timers->isEmpty()) {
            return 0;
        }

        $now = Carbon::now();

        # walk through timers and sum the total duration or calculate the running time from diff in seconds
        return $timers->reduce(function (int $carry, Timer $timer) use ($now) {
            $startedAt = $timer->started_at ? Carbon::parse($timer->started_at) : null;
            if (! $startedAt) {
                return $carry;
            }

            if ($timer->duration !== null) {
                return $carry + (int) $timer->duration;
            }

            # current time, if timer is still running
            # or when the timer was stopped
            $stoppedAt = $timer->stopped_at ? Carbon::parse($timer->stopped_at) : $now;

            return $carry + $stoppedAt->diffInSeconds($startedAt);
        }, 0);
    }
}
