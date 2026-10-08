<?php

namespace App\Http\Requests\Api;

use App\Services\Api\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class SellerListingUpdateRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'custom_price' => ['nullable', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock_quantity' => 'nullable|integer|min:0|max:99999',
            'stock_adjust' => 'nullable|integer|between:-99999,99999',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasPrice = $this->has('custom_price');
            $hasStockQty = $this->has('stock_quantity');
            $hasStockAdj = $this->has('stock_adjust');
            $hasActive = $this->has('is_active');

            if (!$hasPrice && !$hasStockQty && !$hasStockAdj && !$hasActive) {
                $validator->errors()->add('fields', 'At least one field (custom_price, stock_quantity, stock_adjust, or is_active) must be provided.');
            }

            if ($hasStockQty && $hasStockAdj) {
                throw new HttpResponseException(
                    ApiResponse::error(
                        'Cannot provide both stock_quantity and stock_adjust in the same request.',
                        'STOCK_MUTUALLY_EXCLUSIVE',
                        ['stock' => ['Cannot provide both stock_quantity and stock_adjust in the same request.']],
                        422
                    )
                );
            }

            if ($hasPrice && $this->input('custom_price') !== null && (float) $this->input('custom_price') <= 0) {
                $validator->errors()->add('custom_price', 'Custom price must be greater than zero, or null to reset to base price.');
            }
        });
    }
}
