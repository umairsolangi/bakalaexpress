<?php

namespace App\Http\Requests\Api;

class RiderLoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
