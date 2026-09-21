<?php

namespace App\Http\Requests;

use App\Enums\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_name' => trim((string) $this->input('user_name')),
            'user_dept' => trim((string) $this->input('user_dept')),
            'title' => trim((string) $this->input('title')),
            'purpose' => trim((string) $this->input('purpose')),
        ]);
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'user_name' => ['required', 'string', 'max:100'],
            'user_dept' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['nullable', 'string', 'max:2000'],
            'activity_type' => ['required', Rule::enum(ActivityType::class)],
            'attendees_count' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'Tanggal pemesanan tidak boleh berada di masa lalu.',
            'end_time.after' => 'Waktu selesai harus lebih lambat dari waktu mulai.',
        ];
    }
}
