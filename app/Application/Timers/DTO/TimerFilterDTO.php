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
        return [
            'id'          => $this->id,
            'task_id'     => $this->taskId,
            'started_at'  => [
                'after'  => $this->startedAfter,
                'before' => $this->startedBefore,
            ],
            'stopped_at'  => [
                'after'  => $this->stoppedAfter,
                'before' => $this->stoppedBefore,
            ],
            'active'      => $this->active,
        ];
    }
}
