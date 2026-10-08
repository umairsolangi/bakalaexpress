<?php

namespace App\Http\Requests\Api;

class SellerOperatingHoursRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'is_open' => 'required|boolean',
            'opens_at' => 'nullable|date_format:H:i',
            'closes_at' => 'nullable|date_format:H:i',
        ];
    }
}
