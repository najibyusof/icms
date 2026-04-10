<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'staff_id' => ['nullable', 'string', 'max:50', 'unique:users,staff_id'],
            'faculty' => ['nullable', 'string', 'max:150'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'confirmed', Password::min(8)->numbers()],
        ];
    }
}
