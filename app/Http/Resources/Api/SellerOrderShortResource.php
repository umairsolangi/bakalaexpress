<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOrderShortResource extends JsonResource
{
    /**
     * Transform into short summary for seller order list.
     */
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $status = (string) $this->status;

        $itemsCount = $this->relationLoaded('items')
            ? $this->items->count()
            : (int) $this->items()->count();

        // Customer name
        $fullName = (string) ($this->user?->name ?? $this->name ?? 'Customer');
        $nameParts = explode(' ', trim($fullName));
        $customerFirstName = $nameParts[0] ?? $fullName;

        return [
            'id' => (int) $this->id,
            'status' => $status,
            'status_label' => OrderListResource::statusLabel($status),
            'status_step' => OrderListResource::statusStep($status),
            'customer_first_name' => $customerFirstName,
            'item_count' => (int) $itemsCount,
            'unread_messages' => (int) ($this->unread_messages ?? \App\Models\Message::where('order_id', $this->id)->where('sender_type', 'user')->where('is_read', false)->count()),
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
