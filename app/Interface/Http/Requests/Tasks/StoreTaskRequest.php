<?php

namespace App\Interface\Http\Requests\Tasks;

use App\Domain\Tasks\Support\TaskTimerProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'timer_type' => ['required', 'in:pomodoro,custom,hourglass'],
            'target_minutes' => ['nullable', 'integer', 'min:1', 'max:720'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $timerType = $this->input('timer_type');
            $targetMinutes = $this->input('target_minutes');

            if ($timerType === 'custom' && $targetMinutes === null) {
                $validator->errors()->add('target_minutes', 'target_minutes is required for custom timers.');
            }

            if ($timerType === 'hourglass') {
                $minutes = is_numeric($targetMinutes) ? (int) $targetMinutes : null;

                if ($minutes === null || ! TaskTimerProfile::isHourglassPresetMinutes($minutes)) {
                    $validator->errors()->add('target_minutes', 'Hourglass target_minutes must be one of: 60, 90, 120, 240.');
                }
            }

            if ($timerType === 'pomodoro' && $targetMinutes !== null && (int) $targetMinutes !== TaskTimerProfile::pomodoroMinutes()) {
                $validator->errors()->add('target_minutes', 'Pomodoro uses a fixed 25-minute timer.');
            }
        });
    }
}
