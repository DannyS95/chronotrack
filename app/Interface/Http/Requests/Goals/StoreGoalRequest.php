<?php

namespace App\Interface\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Goals\Enums\GoalStatus;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return $this->user()?->can('create', [Goal::class, $project]) ?? false;
    }

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
