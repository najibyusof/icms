<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Admin', 'admin']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = (int) $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'staff_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'staff_id')->ignore($userId)],
            'faculty' => ['nullable', 'string', 'max:150'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'confirmed', Password::min(8)->numbers()],
        ];
    }
}
