<?php

namespace Modules\Programme\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('programme.edit') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'programme_id' => ['required', 'integer', 'exists:programmes,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'total_years' => ['required', 'integer', 'min:1', 'max:20'],
            'semesters_per_year' => ['required', 'integer', 'min:1', 'max:4'],
            'is_active' => ['sometimes', 'boolean'],
            'courses' => ['nullable', 'array'],
            'courses.*.course_id' => ['required_with:courses', 'integer', 'exists:courses,id'],
            'courses.*.year' => ['required_with:courses', 'integer', 'min:1'],
            'courses.*.semester' => ['required_with:courses', 'integer', 'min:1'],
            'courses.*.is_mandatory' => ['sometimes', 'boolean'],
        ];
    }
}
