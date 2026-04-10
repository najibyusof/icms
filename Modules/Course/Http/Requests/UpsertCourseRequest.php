<?php

namespace Modules\Course\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        if ($course) {
            return $this->user()?->can('update', $course) ?? false;
        }

        return $this->user()?->can('course.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $courseId = $this->route('course')?->id;

        return [
            'programme_id' => ['required', 'integer', 'exists:programmes,id'],
            'lecturer_id' => ['nullable', 'integer', 'exists:users,id'],
            'resource_person_id' => ['nullable', 'integer', 'exists:users,id'],
            'vetter_id' => ['nullable', 'integer', 'exists:users,id'],
            'code' => ['required', 'string', 'max:30', Rule::unique('courses', 'code')->ignore($courseId)],
            'name' => ['required', 'string', 'max:255'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],

            'clos' => ['nullable', 'array'],
            'clos.*.statement' => ['required_with:clos', 'string', 'max:1200'],
            'clos.*.bloom_level' => ['required_with:clos', 'string', Rule::in(['C1', 'C2', 'C3', 'C4', 'C5', 'C6'])],

            'requisites' => ['nullable', 'array'],
            'requisites.*.type' => ['nullable', Rule::in(['prerequisite', 'corequisite'])],
            'requisites.*.course_code' => ['required_with:requisites', 'string', 'max:30'],
            'requisites.*.course_name' => ['nullable', 'string', 'max:255'],

            'assessments' => ['nullable', 'array'],
            'assessments.*.component' => ['required_with:assessments', 'string', 'max:100'],
            'assessments.*.weightage' => ['required_with:assessments', 'numeric', 'min:0', 'max:100'],
            'assessments.*.remarks' => ['nullable', 'string', 'max:1000'],

            'topics' => ['nullable', 'array'],
            'topics.*.week_no' => ['required_with:topics', 'integer', 'min:1', 'max:52'],
            'topics.*.title' => ['required_with:topics', 'string', 'max:255'],
            'topics.*.learning_activity' => ['nullable', 'string', 'max:1000'],

            'slt' => ['nullable', 'array'],
            'slt.*.activity' => ['required_with:slt', 'string', 'max:120'],
            'slt.*.f2f_hours' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'slt.*.non_f2f_hours' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'slt.*.independent_hours' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ];
    }
}
