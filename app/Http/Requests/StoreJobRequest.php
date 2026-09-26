<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'employer';
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:30000'],
            'responsibilities' => ['nullable', 'string', 'max:20000'],
            'requirements' => ['nullable', 'string', 'max:20000'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,internship,temporary'],
            'work_arrangement' => ['required', 'in:on_site,hybrid,remote'],
            'experience_level' => ['required', 'in:entry,junior,mid,senior,lead,manager'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'gte:salary_min'],
            'salary_currency' => ['required', 'string', 'size:3'],
            'salary_visible' => ['boolean'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:1000'],
            'application_deadline' => ['nullable', 'date', 'after:today'],
            'publish' => ['boolean'],
            'skill_ids' => ['array', 'max:30'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
        ];
    }
}
