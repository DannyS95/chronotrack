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
        $accumulatedSeconds = $this->accumulatedSeconds();

        return [
            'id' => $this->timer->id,
            'task_id' => $this->timer->task_id,
            'user_id' => $this->timer->user_id,
            'started_at' => $this->timer->started_at?->toISOString(),
            'stopped_at' => $this->timer->stopped_at?->toISOString(),
            'paused_at' => $this->timer->paused_at?->toISOString(),
            'active_since' => $this->activeSince(),
            'accumulated_seconds' => $accumulatedSeconds,
            'accumulated_human' => $this->humanizeSeconds($accumulatedSeconds),
            'is_active' => $this->timer->stopped_at === null,
            'is_paused' => $this->timer->paused_at !== null && $this->timer->stopped_at === null,
            'paused_total_seconds' => $this->timer->paused_total,
            'created_at' => $this->timer->created_at?->toISOString(),
            'updated_at' => $this->timer->updated_at?->toISOString(),
        ];
    }

    private function accumulatedSeconds(): int
    {
        if ($this->timer->duration !== null) {
            return max(0, (int) $this->timer->duration);
        }

        $startedAt = $this->timer->started_at
            ? Carbon::parse($this->timer->started_at)
            : null;

        if ($startedAt === null) {
            return 0;
        }

        $effectiveStop = match (true) {
            $this->timer->stopped_at !== null => Carbon::parse($this->timer->stopped_at),
            $this->timer->paused_at !== null => Carbon::parse($this->timer->paused_at),
            default => Carbon::now(),
        };

        $diff = $effectiveStop->greaterThan($startedAt)
            ? $effectiveStop->diffInSeconds($startedAt)
            : 0;

        $diff -= (int) $this->timer->paused_total;

        return max(0, $diff);
    }

    private function activeSince(): ?string
    {
        if ($this->timer->stopped_at !== null || $this->timer->paused_at !== null) {
            return null;
        }

        return $this->timer->started_at?->toISOString();
    }

    private function humanizeSeconds(int $seconds): ?string
    {
        return $seconds > 0
            ? CarbonInterval::seconds($seconds)->cascade()->forHumans(short: true)
            : null;
    }
}
