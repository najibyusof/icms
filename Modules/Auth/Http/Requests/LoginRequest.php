<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function credentials(): array
    {
        $login = (string) $this->string('login')->value();

        return filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => (string) $this->string('password')->value()]
            : ['staff_id' => $login, 'password' => (string) $this->string('password')->value()];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        if (! Auth::attempt($this->credentials(), $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => __('These credentials do not match our records.'),
            ]);
        }
    }
}
