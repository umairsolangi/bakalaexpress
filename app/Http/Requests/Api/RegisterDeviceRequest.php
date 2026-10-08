<?php

namespace App\Http\Requests\Api;

class RegisterDeviceRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'expo_push_token' => [
                'required',
                'string',
                'regex:/^(ExponentPushToken|ExpoPushToken)\[[A-Za-z0-9_-]+\]$/',
            ],
            'platform' => ['required', 'string', 'in:android,ios'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'expo_push_token.regex' => 'The expo_push_token must be a valid Expo push token (e.g. ExponentPushToken[xxx] or ExpoPushToken[xxx]).',
        ];
    }
}
