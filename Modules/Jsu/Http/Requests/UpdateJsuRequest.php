<?php

namespace Modules\Jsu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJsuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('jsu.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'title'             => ['sometimes', 'string', 'max:255'],
            'total_marks'       => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'duration_minutes'  => ['nullable', 'integer', 'min:1', 'max:600'],
            'notes'             => ['nullable', 'string'],
            'difficulty_config' => ['nullable', 'array'],
            'difficulty_config.lower.target_pct'  => ['nullable', 'integer', 'min:0', 'max:100'],
            'difficulty_config.middle.target_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'difficulty_config.higher.target_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
