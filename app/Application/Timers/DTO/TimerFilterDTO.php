<?php

namespace App\Application\Timers\DTO;

class TimerFilterDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $taskId = null,
        public readonly ?string $startedAfter = null,
        public readonly ?string $startedBefore = null,
        public readonly ?string $stoppedAfter = null,
        public readonly ?string $stoppedBefore = null,
        public readonly ?bool $active = null,
        public readonly ?string $userId = null,
    )  {}

    public function toArray(): array
    {
        return array_filter([
            'id'              => $this->id,
            'task_id'         => $this->taskId,
            'started_after'   => $this->startedAfter,
            'started_before'  => $this->startedBefore,
            'stopped_after'   => $this->stoppedAfter,
            'stopped_before'  => $this->stoppedBefore,
            'active'          => $this->active,
        ], static fn($value) => $value !== null);
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            id: $attributes['id'] ?? null,
            taskId: $attributes['task_id'] ?? null,
            startedAfter: $attributes['started_after'] ?? $attributes['started_at'] ?? null,
            startedBefore: $attributes['started_before'] ?? null,
            stoppedAfter: $attributes['stopped_after'] ?? null,
            stoppedBefore: $attributes['stopped_before'] ?? $attributes['stopped_at'] ?? null,
            active: $attributes['active'] ?? null,
            userId: $attributes['userId'] ?? ($attributes['user_id'] ?? null),
        );
    }
}
