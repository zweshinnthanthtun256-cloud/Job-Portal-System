<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'account_type' => ['required', 'in:job_seeker,employer'],
            'company_name' => ['required_if:account_type,employer', 'nullable', 'string', 'max:160'],
            'industry' => ['required_if:account_type,employer', 'nullable', 'string', 'max:120'],
        ];
    }
}
