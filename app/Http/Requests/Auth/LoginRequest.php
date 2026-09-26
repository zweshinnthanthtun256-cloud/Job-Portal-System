<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email'], 'password' => ['required', 'string'], 'remember' => ['boolean']];
    }

    public function authenticate(): void
    {
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The provided credentials do not match our records.']);
        }

        if (Auth::user()->status !== 'active') {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'This account is not active.']);
        }
    }
}
