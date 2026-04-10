<?php

namespace Modules\Programme\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgrammeRequest extends FormRequest
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
        $programmeId = $this->route('programme')?->id;

        return [
            'code' => ['required', 'string', 'max:30', "unique:programmes,code,{$programmeId}"],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'accreditation_body' => ['nullable', 'string', 'max:100'],
            'duration_semesters' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'programme_chair_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
