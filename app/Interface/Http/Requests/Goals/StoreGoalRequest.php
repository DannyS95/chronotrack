<?php

namespace App\Interface\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Goals\Enums\GoalStatus;

class StoreGoalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', Rule::in(GoalStatus::values())],
            'deadline'    => ['nullable', 'date'],
        ];
    }
}
