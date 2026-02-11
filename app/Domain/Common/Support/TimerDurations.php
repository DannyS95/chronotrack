<?php

namespace App\Domain\Common\Support;

use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

final class TimerDurations
{
    /**
     * @param iterable<int, Timer> $timers
     */
    public static function sumDurationsFromCollection(iterable $timers, ?\DateTimeImmutable $now = null): int
    {
        $now ??= new \DateTimeImmutable();
        $carry = 0;

        foreach ($timers as $timer) {
            $startedAt = self::parseDateTime($timer->started_at);
            if (! $startedAt) {
                continue;
            }

            if ($timer->duration !== null) {
                $carry += max(0, (int) $timer->duration);
                continue;
            }

            $effectiveStop = $timer->paused_at
                ? self::parseDateTime($timer->paused_at)
                : (self::parseDateTime($timer->stopped_at) ?? $now);

            if ($effectiveStop === null) {
                continue;
            }

            $diff = max(0, $effectiveStop->getTimestamp() - $startedAt->getTimestamp());

            $diff -= (int) $timer->paused_total;

            $carry += max(0, $diff);
        }

        return $carry;
    }

    /**
     * @param iterable<int, Timer> $timers
     */
    public static function determineActiveSinceFromCollection(iterable $timers): ?\DateTimeImmutable
    {
        $firstTimer = null;

        foreach ($timers as $timer) {
            $startedAt = self::parseDateTime($timer->started_at);
            if ($startedAt === null) {
                continue;
            }

            if ($firstTimer === null || $startedAt < $firstTimer) {
                $firstTimer = $startedAt;
            }
        }

        return $firstTimer;
    }

    /**
     * @param iterable<int, TaskSnapshot> $snapshots
     */
    public static function sumDurationsFromSnapshots(iterable $snapshots): int
    {
        $total = 0;

        foreach ($snapshots as $task) {
            $total += max(0, $task->accumulatedSeconds);
        }

        return $total;
    }

    /**
     * @param iterable<int, TaskSnapshot> $snapshots
     */
    public static function determineActiveSinceFromSnapshots(iterable $snapshots): ?string
    {
        $earliest = null;

        foreach ($snapshots as $task) {
            $activeSince = self::parseDateTime($task->activeSince);
            if ($activeSince === null) {
                continue;
            }

            if ($earliest === null || $activeSince < $earliest) {
                $earliest = $activeSince;
            }
        }

        return $earliest?->format('Y-m-d H:i:s');
    }

    public static function humanizeSeconds(int $seconds): ?string
    {
        if ($seconds <= 0) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours.'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes.'m';
        }
        if ($remainingSeconds > 0 || $parts === []) {
            $parts[] = $remainingSeconds.'s';
        }

        return implode(' ', $parts);
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
