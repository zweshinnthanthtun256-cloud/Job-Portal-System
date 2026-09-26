<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'employer';
    }

    public function rules(): array
    {
        return ['date' => ['required', 'date', 'after_or_equal:today'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'after:start_time'], 'timezone' => ['required', 'timezone'], 'type' => ['required', 'in:phone,video,on_site,technical,hr,final'], 'location' => ['nullable', 'string', 'max:255'], 'meeting_url' => ['nullable', 'url', 'required_if:type,video'], 'interviewer' => ['nullable', 'string', 'max:120'], 'notes' => ['nullable', 'string', 'max:3000']];
    }
}
