<?php

namespace App\Interface\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

final class TaskFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'     => ['nullable', 'string'],
            'from'      => ['nullable', 'date'],
            'to'        => ['nullable', 'date'],
            'sort_by'   => ['nullable', 'in:due_at,last_activity_at,created_at'],
            'order'     => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
