<?php

namespace App\Domain\Tasks\Support;

use InvalidArgumentException;

final class TaskTimerProfile
{
    private const POMODORO_MINUTES = 25;

    /** @var array<int, int> */
    private const HOURGLASS_PRESET_MINUTES = [60, 90, 120, 240];

    /**
     * @return array{timer_type:string,target_duration_seconds:int}
     */
    public static function normalize(string $timerType, mixed $targetMinutes): array
    {
        $type = strtolower(trim($timerType));

        return match ($type) {
            'pomodoro' => [
                'timer_type' => 'pomodoro',
                'target_duration_seconds' => self::POMODORO_MINUTES * 60,
            ],
            'custom' => [
                'timer_type' => 'custom',
                'target_duration_seconds' => self::requireMinutes($targetMinutes, 'custom') * 60,
            ],
            'hourglass' => [
                'timer_type' => 'hourglass',
                'target_duration_seconds' => self::requireHourglassPreset($targetMinutes) * 60,
            ],
            default => throw new InvalidArgumentException('Unsupported timer type.'),
        };
    }

    public static function isHourglassPresetMinutes(int $minutes): bool
    {
        return in_array($minutes, self::HOURGLASS_PRESET_MINUTES, true);
    }

    public static function pomodoroMinutes(): int
    {
        return self::POMODORO_MINUTES;
    }

    private static function requireMinutes(mixed $targetMinutes, string $timerType): int
    {
        if ($targetMinutes === null || $targetMinutes === '') {
            throw new InvalidArgumentException("target_minutes is required for {$timerType} timers.");
        }

        if (! is_numeric($targetMinutes)) {
            throw new InvalidArgumentException('target_minutes must be numeric.');
        }

        $minutes = (int) $targetMinutes;

        if ($minutes < 1 || $minutes > 720) {
            throw new InvalidArgumentException('target_minutes must be between 1 and 720.');
        }

        return $minutes;
    }

    private static function requireHourglassPreset(mixed $targetMinutes): int
    {
        $minutes = self::requireMinutes($targetMinutes, 'hourglass');

        if (! self::isHourglassPresetMinutes($minutes)) {
            throw new InvalidArgumentException('Hourglass target_minutes must be one of: 60, 90, 120, 240.');
        }

        return $minutes;
    }
}
