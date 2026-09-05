<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('task')) {
            Gate::authorize('update', $this->route('task'));
        }

        return $this->user() !== null;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
