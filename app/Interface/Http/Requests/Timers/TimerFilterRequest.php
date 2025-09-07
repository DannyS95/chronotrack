<?php

namespace App\Interface\Http\Requests\Timers;

use Illuminate\Foundation\Http\FormRequest;

class TimerFilterRequest extends FormRequest
{
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
        if ($this->has('active')) {
            $this->merge([
                'active' => filter_var($this->active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
