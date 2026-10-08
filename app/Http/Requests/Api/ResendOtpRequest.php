<?php

namespace App\Http\Requests\Api;

class ResendOtpRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
