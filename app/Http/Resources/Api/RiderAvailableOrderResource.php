<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderAvailableOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array for available delivery orders.
     */
    public function toArray(Request $request): array
    {
        /** @var Order $this */
        $seller = $this->seller;

        $sellerImage = $seller?->profile_image;
        if ($sellerImage && !str_starts_with($sellerImage, 'http://') && !str_starts_with($sellerImage, 'https://')) {
            $sellerImage = asset('storage/' . ltrim($sellerImage, '/'));
        } elseif (!$sellerImage && $seller) {
            $sellerImage = $seller->display_profile_image ?? null;
        }

        $itemCount = $this->relationLoaded('items')
            ? $this->items->count()
            : (int) $this->items()->count();

        // Privacy-preserving customer area hint (delivery_near_area and delivery_sector when present; otherwise null)
        $customerAreaHint = self::extractAreaHint($this->address, $this->user, $this->resource);

        return [
            'id' => (int) $this->id,
            'seller' => [
                'id' => $seller ? (int) $seller->id : null,
                'name' => (string) ($seller?->name ?? 'Bakala Express Merchant'),
                'image' => $sellerImage,
                'area' => $seller?->area ? (string) $seller->area : null,
                'sector' => $seller?->sector ? (string) $seller->sector : null,
                'full_address' => $seller?->full_address ? (string) $seller->full_address : null,
            ],
            'item_count' => (int) $itemCount,
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'amount_to_collect' => number_format((float) $this->total_amount, 2, '.', ''),
            'delivery_charges' => number_format((float) ($this->delivery_charges ?? 0), 2, '.', ''),
            'ready_since' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'customer_area_hint' => $customerAreaHint,
        ];
    }

    /**
     * Extract a privacy-safe customer area hint without exposing house or street doorstep details.
     */
    public static function extractAreaHint(?string $address, ?\App\Models\User $user = null, $order = null): ?string
    {
        $nearArea = $order?->delivery_near_area ?? null;
        $sector = $order?->delivery_sector ?? null;

        if ($nearArea && $sector) {
            return "{$nearArea}, Sector {$sector}";
        }
        if ($nearArea) {
            return (string) $nearArea;
        }
        if ($sector) {
            return "Sector {$sector}";
        }

        return null;
    }
}
