<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\ValidationException;

class CartValidateRequest extends BaseApiRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $disallowed = ['price', 'total', 'discount', 'status', 'delivery_charges', 'subtotal'];
        foreach ($disallowed as $field) {
            if ($this->has($field)) {
                throw ValidationException::withMessages([
                    $field => ["The {$field} field cannot be supplied by the client."],
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.listing_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            if (!is_array($items)) {
                return;
            }

            $listingIds = [];
            foreach ($items as $index => $item) {
                if (isset($item['listing_id'])) {
                    $id = $item['listing_id'];
                    if (in_array($id, $listingIds, true)) {
                        $validator->errors()->add(
                            "items.{$index}.listing_id",
                            "Duplicate listing ID {$id} found. Please combine quantities."
                        );
                    }
                    $listingIds[] = $id;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Cart items are required.',
            'items.min' => 'Cart must contain at least 1 item.',
            'items.max' => 'Cart cannot exceed 30 items.',
            'items.*.listing_id.required' => 'Each item must have a listing_id.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 20 per item.',
        ];
    }
}
