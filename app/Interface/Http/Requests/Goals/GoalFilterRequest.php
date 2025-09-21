<?php

namespace App\Interface\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

final class GoalFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'uuid'],
            'status'          => ['sometimes', 'in:active,dormant,dropped,complete'],
            'deadline'        => ['sometimes', 'date'],
            'completion_rule' => ['sometimes', 'in:task_based,deadline_based,hybrid'],
        ];
    }
}
