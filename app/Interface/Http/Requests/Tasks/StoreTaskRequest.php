<?php

namespace App\Interface\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

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
}
