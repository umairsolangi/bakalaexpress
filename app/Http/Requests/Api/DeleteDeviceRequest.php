<?php

namespace App\Http\Requests\Api;

class DeleteDeviceRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'expo_push_token' => ['required', 'string'],
        ];
    }
}
