<?php

namespace App\Http\Requests\Api;

class SellerOrderListRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'group' => 'nullable|string|in:pending,active,completed,cancelled,all',
            'status' => 'nullable|string|in:pending,confirmed_by_seller,preparing,ready_for_pickup,assigned_to_rider,picked_up,delivered,completed,cancelled,rejected',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ];
    }
}
