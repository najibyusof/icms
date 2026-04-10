<?php

namespace Modules\Workflow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordWorkflowDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('workflow.review') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'workflow_id' => ['required', 'integer', 'exists:workflow_instances,id'],
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
