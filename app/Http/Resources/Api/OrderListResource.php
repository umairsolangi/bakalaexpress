<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Short form order resource for active orders and history lists.
 */
class OrderListResource extends JsonResource
{
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'confirmed_by_seller' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready_for_pickup' => 'Ready for Pickup',
            'assigned_to_rider' => 'Rider Assigned',
            'picked_up' => 'Picked Up',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public static function statusStep(string $status): int
    {
        return match ($status) {
            'pending' => 1,
            'confirmed_by_seller' => 2,
            'preparing' => 3,
            'ready_for_pickup' => 4,
            'assigned_to_rider', 'picked_up' => 5,
            'delivered', 'completed' => 6,
            'cancelled', 'rejected' => 0,
            default => 0,
        };
    }

    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $seller = $this->seller;
        $sellerImage = $seller?->profile_image;
        if ($sellerImage && !str_starts_with($sellerImage, 'http://') && !str_starts_with($sellerImage, 'https://')) {
            $sellerImage = asset('storage/' . ltrim($sellerImage, '/'));
        }

        $itemsCount = $this->relationLoaded('items')
            ? $this->items->count()
            : $this->items()->count();

        return [
            'id' => (int) $this->id,
            'status' => (string) $this->status,
            'status_label' => self::statusLabel((string) $this->status),
            'status_step' => self::statusStep((string) $this->status),
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'seller' => [
                'id' => $seller ? (int) $seller->id : null,
                'name' => (string) ($seller?->name ?? 'Bakala Express Merchant'),
                'image' => $sellerImage,
            ],
            'item_count' => (int) $itemsCount,
            'unread_messages' => (int) ($this->unread_messages ?? \App\Models\Message::where('order_id', $this->id)->where('sender_type', 'seller')->where('is_read', false)->count()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
