<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateRoomRequest extends StoreRoomRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['code'] = [
            'required',
            'string',
            'max:20',
            'regex:/^[A-Za-z0-9_-]+$/',
            Rule::unique('rooms', 'code')->ignore($this->route('room')),
        ];

        return $rules;
    }
}
