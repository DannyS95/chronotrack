<?php

namespace App\Interface\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policies can refine later
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline'    => ['nullable', 'date'],
            'status'      => ['nullable', 'in:active,dormant,dropped,complete'],
        ];
    }
}
