<?php

namespace App\Http\Requests\Api;

class AdminLoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
