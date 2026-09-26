<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'job_seeker';
    }

    public function rules(): array
    {
        return [
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'expected_salary' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'availability_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
