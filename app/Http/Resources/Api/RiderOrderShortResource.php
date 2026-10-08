<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderOrderShortResource extends JsonResource
{
    /**
     * Transform the resource into a short summary array for active orders and dashboard.
     */
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $status = (string) $this->status;
        $seller = $this->seller;

        $itemCount = $this->relationLoaded('items')
            ? $this->items->count()
            : (int) $this->items()->count();

        return [
            'id' => (int) $this->id,
            'status' => $status,
            'status_label' => OrderListResource::statusLabel($status),
            'seller_name' => (string) ($seller?->name ?? 'Bakala Express Merchant'),
            'customer_name' => (string) ($this->user?->name ?? 'Customer'),
            'customer_phone' => (string) $this->phone,
            'customer_address' => (string) $this->address,
            'item_count' => (int) $itemCount,
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'amount_to_collect' => number_format((float) $this->total_amount, 2, '.', ''),
            'can_pickup' => ($status === 'assigned_to_rider'),
            'can_deliver' => ($status === 'picked_up'),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
