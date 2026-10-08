<?php

namespace App\Http\Requests\Api;

class SellerRejectOrderRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:255',
        ];
    }
}
