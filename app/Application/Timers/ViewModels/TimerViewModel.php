<?php

namespace App\Application\Timers\ViewModels;

use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Carbon\Carbon;
use Carbon\CarbonInterval;

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
            'paused_at' => $this->timer->paused_at?->toISOString(),
            'duration' => $this->timer->duration ?? $elapsedSeconds,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_human' => $this->humanizeSeconds($elapsedSeconds),
            'is_active' => $this->timer->stopped_at === null,
            'is_paused' => $this->timer->paused_at !== null && $this->timer->stopped_at === null,
            'paused_total_seconds' => $this->timer->paused_total,
            'created_at' => $this->timer->created_at?->toISOString(),
            'updated_at' => $this->timer->updated_at?->toISOString(),
        ];
    }

    private function elapsedSeconds(): int
    {
        if ($this->timer->duration !== null && $this->timer->duration > 0) {
            return (int) $this->timer->duration;
        }

        $startedAt = $this->timer->started_at
            ? Carbon::parse($this->timer->started_at)
            : null;

        if ($startedAt === null) {
            return 0;
        }

        $endTimestamp = match (true) {
            $this->timer->stopped_at !== null => Carbon::parse($this->timer->stopped_at)->getTimestamp(),
            $this->timer->paused_at !== null => Carbon::parse($this->timer->paused_at)->getTimestamp(),
            default => Carbon::now()->getTimestamp(),
        };

        return max(0, $endTimestamp - $startedAt->getTimestamp());
    }

    private function humanizeSeconds(int $seconds): ?string
    {
        return $seconds > 0
            ? CarbonInterval::seconds($seconds)->cascade()->forHumans(short: true)
            : null;
    }
}
