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
        ];
    }
}
