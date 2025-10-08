<?php

namespace App\Interface\Http\Requests\Timers;

use Illuminate\Foundation\Http\FormRequest;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

class TimerFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task|null $task */
        $task = $this->route('task');

        return $this->user()?->can('viewAny', [Timer::class, $task]) ?? false;
    }

    public function rules(): array
    {
        return [
            'id'             => ['nullable', 'uuid'],
            'task_id'        => ['nullable', 'uuid'],
            'started_after'  => ['nullable', 'date'],
            'started_before' => ['nullable', 'date'],
            'stopped_after'  => ['nullable', 'date'],
            'stopped_before' => ['nullable', 'date'],
            'active'         => ['nullable', 'boolean'], // true = running, false = completed
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('started_at') && ! $this->has('started_after')) {
            $this->merge(['started_after' => $this->input('started_at')]);
        }

        if ($this->has('stopped_at') && ! $this->has('stopped_before')) {
            $this->merge(['stopped_before' => $this->input('stopped_at')]);
        }

        if ($this->has('active')) {
            $this->merge([
                'active' => filter_var($this->active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
