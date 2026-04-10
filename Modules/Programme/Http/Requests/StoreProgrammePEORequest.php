<?php

namespace Modules\Programme\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammePEORequest extends FormRequest
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
            'code' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:1000'],
            'sequence_order' => ['required', 'integer', 'min:1'],
        ];
    }
}
