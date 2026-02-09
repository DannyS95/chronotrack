<?php

namespace App\Interface\Http\Support;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use Carbon\Carbon;

final class GoalResponseMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toGoalResponse(Goal $goal): array
    {
        $deadline = $goal->deadline instanceof \DateTimeInterface
            ? Carbon::instance($goal->deadline)
            : ($goal->deadline ? Carbon::parse((string) $goal->deadline) : null);

        $completedAt = $goal->completed_at instanceof \DateTimeInterface
            ? Carbon::instance($goal->completed_at)->toIso8601String()
            : (is_string($goal->completed_at) ? $goal->completed_at : null);

        $remainingSeconds = $deadline && $goal->status !== 'complete'
            ? Carbon::now()->diffInSeconds($deadline, false)
            : null;

        return [
            'id' => $goal->id,
            'summary' => $goal->title,
            'description' => $goal->description,
            'goal_date' => $deadline?->toDateString(),
            'deadline_at' => $deadline?->toIso8601String(),
            'status' => $goal->status,
            'completed_at' => $completedAt,
            'time_remaining_seconds' => $remainingSeconds,
            'is_overdue' => $remainingSeconds !== null && $remainingSeconds < 0,
            'created_at' => $goal->created_at?->toISOString(),
            'updated_at' => $goal->updated_at?->toISOString(),
        ];
    }
}
