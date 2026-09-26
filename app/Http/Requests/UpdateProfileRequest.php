<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'job_seeker';
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'], 'last_name' => ['required', 'string', 'max:80'], 'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'], 'gender' => ['nullable', 'string', 'max:30'], 'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'], 'headline' => ['nullable', 'string', 'max:180'], 'summary' => ['nullable', 'string', 'max:5000'], 'current_position' => ['nullable', 'string', 'max:160'],
            'experience_level' => ['nullable', 'in:entry,junior,mid,senior,lead,manager'], 'expected_salary' => ['nullable', 'integer', 'min:0'], 'preferred_employment_type' => ['nullable', 'string', 'max:40'],
            'preferred_location' => ['nullable', 'string', 'max:120'], 'availability' => ['nullable', 'string', 'max:80'], 'linkedin_url' => ['nullable', 'url', 'max:255'], 'github_url' => ['nullable', 'url', 'max:255'], 'portfolio_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
