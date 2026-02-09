<?php

namespace App\Interface\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

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
            'timer_type' => ['required', 'in:pomodoro,custom,hourglass'],
            'target_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:720'],
        ];
    }
}
