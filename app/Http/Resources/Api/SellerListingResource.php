<?php

namespace App\Http\Resources\Api;

use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopProduct $this */
        $global = $this->globalProduct;
        $category = $global?->category;

        $imageUrl = $global?->default_image;
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
        }

        $basePrice = (float) ($global?->base_price ?? 0);
        $customPrice = $this->custom_price !== null ? (float) $this->custom_price : null;
        $effectivePrice = $customPrice ?? $basePrice;

        $priceDiffers = $customPrice !== null && abs($customPrice - $basePrice) > 0.001;

        return [
            'listing_id' => (int) $this->id,
            'global_product_id' => (int) $this->global_product_id,
            'name' => (string) ($global?->name ?? 'Unknown Product'),
            'unit_type' => (string) ($global?->unit_type ?? 'piece'),
            'image_url' => $imageUrl,
            'category' => [
                'id' => (int) ($category?->id ?? 0),
                'name' => (string) ($category?->name ?? 'Uncategorized'),
            ],
            'base_price' => number_format($basePrice, 2, '.', ''),
            'custom_price' => $customPrice !== null ? number_format($customPrice, 2, '.', '') : null,
            'effective_price' => number_format($effectivePrice, 2, '.', ''),
            'stock_quantity' => (int) $this->stock_quantity,
            'is_active' => (bool) $this->is_active,
            'price_differs_from_base' => (bool) $priceDiffers,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
