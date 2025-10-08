<?php

namespace App\Application\Timers\ViewModels;

use App\Domain\Common\Support\TimerDurations;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

final class TimerViewModel
{
    private function __construct(private readonly Timer $timer) {}

    public static function fromModel(Timer $timer): self
    {
        return new self($timer);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $elapsedSeconds = $this->elapsedSeconds();

        return [
            'id' => $this->timer->id,
            'task_id' => $this->timer->task_id,
            'user_id' => $this->timer->user_id,
            'started_at' => $this->timer->started_at?->toISOString(),
            'stopped_at' => $this->timer->stopped_at?->toISOString(),
            'duration' => $this->timer->duration,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_human' => TimerDurations::humanizeSeconds($elapsedSeconds),
            'is_active' => $this->timer->stopped_at === null,
            'created_at' => $this->timer->created_at?->toISOString(),
            'updated_at' => $this->timer->updated_at?->toISOString(),
        ];
    }

    private function elapsedSeconds(): int
    {
        return TimerDurations::sumDurationsFromCollection(collect([$this->timer]));
    }
}
