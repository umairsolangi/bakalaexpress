<?php

namespace App\Http\Requests\Api;

class VerifyOtpRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ];
    }
}
