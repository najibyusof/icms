<?php

namespace Modules\Jsu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlueprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('jsu.update') ?? false;
    }

    public function rules(): array
    {
        $maxBloom = count(config('jsu.bloom_levels', [])) ?: 6;

        return [
            'question_no'       => ['required', 'integer', 'min:1'],
            'clo_id'            => ['nullable', 'integer', 'exists:course_clos,id'],
            'topic'             => ['nullable', 'string', 'max:200'],
            'bloom_level'       => ['required', 'integer', 'min:1', "max:{$maxBloom}"],
            'marks'             => ['required', 'numeric', 'min:0', 'max:999.99'],
            'weight_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}
