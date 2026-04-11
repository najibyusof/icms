<?php

namespace Modules\Jsu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJsuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('jsu.create') ?? false;
    }

    public function rules(): array
    {
        $examTypes = implode(',', config('jsu.exam_types', []));

        return [
            'course_id'         => ['required', 'integer', 'exists:courses,id'],
            'academic_session'  => ['required', 'string', 'max:30'],
            'exam_type'         => ['required', 'string', "in:{$examTypes}"],
            'title'             => ['required', 'string', 'max:255'],
            'total_marks'       => ['required', 'integer', 'min:1', 'max:1000'],
            'duration_minutes'  => ['nullable', 'integer', 'min:1', 'max:600'],
            'notes'             => ['nullable', 'string'],
            'difficulty_config' => ['nullable', 'array'],
            'difficulty_config.lower.target_pct'  => ['nullable', 'integer', 'min:0', 'max:100'],
            'difficulty_config.middle.target_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'difficulty_config.higher.target_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
