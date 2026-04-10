<?php

namespace Modules\Group\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('group')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $groupId = $this->route('group')?->id;

        return [
            'programme_id' => ['required', 'integer', 'exists:programmes,id'],
            'coordinator_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:100'],
            'intake_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
