<?php

namespace App\Application\Timers\DTOs;

use Carbon\CarbonImmutable;

final class TimerDTO
{
    public function __construct(
        public string $id,
        public string $taskId,
        public ?CarbonImmutable $startedAt,
        public ?CarbonImmutable $stoppedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'task_id'     => $this->taskId,
            'started_at'  => $this->startedAt?->toIso8601String(),
            'stopped_at'  => $this->stoppedAt?->toIso8601String(),
            'is_running'  => $this->stoppedAt === null,
            'duration'    => ($this->startedAt && $this->stoppedAt)
                ? $this->stoppedAt->diffInSeconds($this->startedAt)
                : null,
        ];
    }
}
