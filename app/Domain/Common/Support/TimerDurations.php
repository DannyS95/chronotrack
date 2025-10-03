<?php

namespace App\Domain\Common\Support;

use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;

final class TimerDurations
{
    /**
     * @param Collection<int, Timer> $timers
     */
    public static function sumDurationsFromCollection(Collection $timers, ?Carbon $now = null): int
    {
        if ($timers->isEmpty()) {
            return 0;
        }

        $now ??= Carbon::now();

        return (int) $timers->reduce(function (int $carry, Timer $timer) use ($now) {
            $startedAt = $timer->started_at ? Carbon::parse($timer->started_at) : null;
            if (! $startedAt) {
                return $carry;
            }

            if ($timer->duration !== null) {
                return $carry + max(0, (int) $timer->duration);
            }

            $stoppedAt = $timer->stopped_at ? Carbon::parse($timer->stopped_at) : $now;

            $diff = $stoppedAt->greaterThan($startedAt)
                ? $stoppedAt->diffInSeconds($startedAt)
                : 0;

            return $carry + $diff;
        }, 0);
    }

    /**
     * @param Collection<int, Timer> $timers
     */
    public static function determineActiveSinceFromCollection(Collection $timers): ?Carbon
    {
        if ($timers->isEmpty()) {
            return null;
        }

        $firstTimer = $timers
            ->map(fn(Timer $timer) => $timer->started_at ? Carbon::parse($timer->started_at) : null)
            ->filter()
            ->sort()
            ->first();

        return $firstTimer instanceof Carbon ? $firstTimer : null;
    }

    /**
     * @param Collection<int, TaskSnapshot> $snapshots
     */
    public static function sumDurationsFromSnapshots(Collection $snapshots): int
    {
        if ($snapshots->isEmpty()) {
            return 0;
        }

        return (int) $snapshots->reduce(
            fn(int $carry, TaskSnapshot $task) => $carry + max(0, $task->activeDurationSeconds),
            0
        );
    }

    /**
     * @param Collection<int, TaskSnapshot> $snapshots
     */
    public static function determineActiveSinceFromSnapshots(Collection $snapshots): ?string
    {
        if ($snapshots->isEmpty()) {
            return null;
        }

        $earliest = $snapshots
            ->map(fn(TaskSnapshot $task) => $task->activeSince ? Carbon::parse($task->activeSince) : null)
            ->filter()
            ->sort()
            ->first();

        return $earliest instanceof Carbon
            ? $earliest->format('Y-m-d H:i:s')
            : null;
    }

    public static function humanizeSeconds(int $seconds): ?string
    {
        return $seconds > 0
            ? CarbonInterval::seconds($seconds)->cascade()->forHumans(short: true)
            : null;
    }
}
