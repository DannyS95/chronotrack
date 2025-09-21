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
            'status'          => ['sometimes', 'in:active,dormant,dropped,complete'],
            'deadline_before' => ['sometimes', 'date'],
            'deadline_after'  => ['sometimes', 'date'],
            'completion_rule' => ['sometimes', 'in:task_based,deadline_based,hybrid'],
        ];
    }
}
