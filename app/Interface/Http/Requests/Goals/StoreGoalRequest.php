<?php

namespace App\Interface\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Goals\Enums\GoalStatus;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'summary'     => ['nullable', 'string', 'max:255', 'required_without:title'],
            'title'       => ['nullable', 'string', 'max:255', 'required_without:summary'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', Rule::in(GoalStatus::values())],
            'goal_date'   => ['nullable', 'date_format:Y-m-d', 'required_without:deadline'],
            'deadline'    => ['nullable', 'date', 'required_without:goal_date'],
        ];
    }
}
