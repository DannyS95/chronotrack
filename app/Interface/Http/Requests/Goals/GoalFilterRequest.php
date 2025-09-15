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
            'status'    => ['nullable', 'in:active,dormant,dropped,complete'],
            'from'      => ['nullable', 'date'],  // deadline after
            'to'        => ['nullable', 'date'],  // deadline before
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by'   => ['nullable', 'in:deadline,created_at'],
            'order'     => ['nullable', 'in:asc,desc'],
        ];
    }
}
