<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomDisplayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id', 'unique:room_displays,room_id'],
            'display_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return ['room_id.unique' => 'Ruangan ini sudah memiliki perangkat display.'];
    }
}
