<?php

namespace Modules\Examination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExaminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('examination.submit') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'group_id' => ['required', 'integer', 'exists:academic_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
