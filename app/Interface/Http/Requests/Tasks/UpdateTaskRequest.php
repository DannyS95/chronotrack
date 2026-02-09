<?php

namespace App\Interface\Http\Requests\Tasks;

use App\Domain\Tasks\Support\TaskTimerProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:active,done'],
            'goal_id' => ['sometimes', 'uuid'],
            'timer_type' => ['sometimes', 'in:pomodoro,custom,hourglass'],
            'target_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:720'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasTimerType = $this->has('timer_type');
            $hasTargetMinutes = $this->has('target_minutes');

            if ($hasTargetMinutes && ! $hasTimerType) {
                $validator->errors()->add('timer_type', 'timer_type is required when updating target_minutes.');
                return;
            }

            if (! $hasTimerType) {
                return;
            }

            $timerType = $this->input('timer_type');
            $targetMinutes = $this->input('target_minutes');

            if ($timerType === 'custom' && ! $hasTargetMinutes) {
                $validator->errors()->add('target_minutes', 'target_minutes is required when timer_type is custom.');
            }

            if ($timerType === 'hourglass') {
                $minutes = is_numeric($targetMinutes) ? (int) $targetMinutes : null;

                if ($minutes === null || ! TaskTimerProfile::isHourglassPresetMinutes($minutes)) {
                    $validator->errors()->add('target_minutes', 'Hourglass target_minutes must be one of: 60, 90, 120, 240.');
                }
            }

            if ($timerType === 'pomodoro' && $hasTargetMinutes && $targetMinutes !== null && (int) $targetMinutes !== TaskTimerProfile::pomodoroMinutes()) {
                $validator->errors()->add('target_minutes', 'Pomodoro uses a fixed 25-minute timer.');
            }
        });
    }
}
