<?php

namespace App\Http\Resources\Api;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Snapshot of an order item at time of purchase.
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var OrderItem $this */
        $imageUrl = $this->item_image;
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
        }

        $unitPrice = (float) $this->price;
        $qty = (int) $this->quantity;
        $lineTotal = round($unitPrice * $qty, 2);

        return [
            'id' => (int) $this->id,
            'listing_id' => $this->shop_product_id ? (int) $this->shop_product_id : null,
            'item_name' => (string) $this->item_name,
            'unit_type' => $this->unit_type ? (string) $this->unit_type : null,
            'item_image' => $imageUrl,
            'quantity' => $qty,
            'unit_price' => number_format($unitPrice, 2, '.', ''),
            'line_total' => number_format($lineTotal, 2, '.', ''),
        ];
    }
}
