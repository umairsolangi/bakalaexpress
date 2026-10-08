<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\ValidationException;

class PlaceOrderRequest extends BaseApiRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $disallowed = [
            'price',
            'total',
            'discount',
            'status',
            'delivery_charges',
            'subtotal',
            'transaction_id',
        ];

        foreach ($disallowed as $field) {
            if ($this->has($field)) {
                throw ValidationException::withMessages([
                    $field => ["The {$field} field cannot be specified in the request body."],
                ]);
            }
        }

        $user = $this->user();

        if (!$this->filled('address')) {
            $savedAddress = trim(collect([$user?->address, $user?->address2, $user?->city])->filter()->implode(', '));
            if ($savedAddress !== '') {
                $this->merge(['address' => $savedAddress]);
            }
        }

        if (!$this->filled('phone')) {
            $savedPhone = $user?->mobile ?? '';
            if ($savedPhone !== '') {
                $this->merge(['phone' => $savedPhone]);
            }
        }

        if ($this->has('promo_code') && is_string($this->input('promo_code'))) {
            $cleanPromo = strtoupper(trim((string) $this->input('promo_code')));
            $this->merge(['promo_code' => $cleanPromo === '' ? null : $cleanPromo]);
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.listing_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:15'],
            'delivery_instructions' => ['nullable', 'string', 'max:255'],
            'delivery_sector' => ['nullable', 'string', \Illuminate\Validation\Rule::in(\App\Support\Api\LocationOptions::SECTORS)],
            'delivery_near_area' => ['nullable', 'string', \Illuminate\Validation\Rule::in(\App\Support\Api\LocationOptions::NEAR_AREAS)],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $idempotencyKey = $this->header('Idempotency-Key');
            if (empty($idempotencyKey) || !is_string($idempotencyKey) || strlen($idempotencyKey) < 16 || strlen($idempotencyKey) > 64) {
                $validator->errors()->add(
                    'idempotency_key',
                    'A valid Idempotency-Key header (16 to 64 characters) is required.'
                );
            }

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
            'address.required' => 'Delivery address is required. Please provide an address or update your profile.',
            'phone.required' => 'Phone number is required. Please provide a phone number or update your profile.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
