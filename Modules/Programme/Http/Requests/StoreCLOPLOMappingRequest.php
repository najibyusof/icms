<?php

namespace Modules\Programme\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCLOPLOMappingRequest extends FormRequest
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
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'programme_plo_id' => ['required', 'integer', 'exists:programme_plos,id'],
            'clo_code' => ['required', 'string', 'max:30'],
            'alignment_notes' => ['nullable', 'string', 'max:1000'],
            'bloom_level' => ['required', 'integer', 'min:1', 'max:6'],
        ];
    }
}
