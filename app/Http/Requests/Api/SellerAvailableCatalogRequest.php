<?php

namespace App\Http\Requests\Api;

class SellerAvailableCatalogRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:30',
        ];
    }
}
