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
            'goal_date'       => ['sometimes', 'date_format:Y-m-d'],
            'deadline'        => ['sometimes', 'date'],
            'sort_by'         => ['sometimes', 'in:deadline,created_at,updated_at,title'],
            'order'           => ['sometimes', 'in:asc,desc'],
            'per_page'        => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

}
