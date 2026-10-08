<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderHistoryOrderResource extends JsonResource
{
    /**
     * Transform the order into a short summary array for fulfillment history.
     */
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $status = (string) $this->status;
        $seller = $this->seller;

        $itemCount = $this->relationLoaded('items')
            ? $this->items->count()
            : (int) $this->items()->count();

        $proofImage = null;
        if ($this->delivery_proof_image) {
            $proof = $this->delivery_proof_image;
            $proofImage = (!str_starts_with($proof, 'http://') && !str_starts_with($proof, 'https://'))
                ? asset('storage/' . ltrim($proof, '/'))
                : $proof;
        }

        $fullName = trim((string) ($this->user?->name ?? 'Customer'));
        $firstName = explode(' ', $fullName)[0];
        $customerAreaHint = RiderAvailableOrderResource::extractAreaHint($this->address, $this->user, $this->resource);

        return [
            'id' => (int) $this->id,
            'status' => $status,
            'status_label' => OrderListResource::statusLabel($status),
            'seller_name' => (string) ($seller?->name ?? 'Bakala Express Merchant'),
            'customer_name' => (string) $firstName,
            'customer_area_hint' => $customerAreaHint,
            'item_count' => (int) $itemCount,
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'delivery_charges' => number_format((float) ($this->delivery_charges ?? 0), 2, '.', ''),
            'delivery_proof_image' => $proofImage,
            'delivered_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
