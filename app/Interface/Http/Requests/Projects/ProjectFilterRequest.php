<?php

namespace App\Interface\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'deadlineFrom' => 'nullable|date',
            'deadlineTo' => 'nullable|date',
            'archived' => 'nullable|boolean',
            'sortBy' => 'nullable|in:name,created_at,deadline',
            'sortDirection' => 'nullable|in:asc,desc',
            'perPage' => 'nullable|integer|min:1|max:100',
        ];
    }
}
