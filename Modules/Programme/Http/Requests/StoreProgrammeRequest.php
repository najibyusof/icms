<?php

namespace Modules\Programme\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('programme.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:programmes,code'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:50'],
            'duration_semesters' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
