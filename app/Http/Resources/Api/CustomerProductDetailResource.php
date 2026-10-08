<?php

namespace App\Http\Resources\Api;

use App\Models\ShopProduct;
use App\Support\Api\FavoriteLookup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detailed catalog product listing with shop summary.
 */
class CustomerProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopProduct $this */
        $global = $this->globalProduct;
        $seller = $this->seller;

        $price = (float) ($this->custom_price ?? $global?->base_price ?? 0);
        $stock = (int) ($this->stock_quantity ?? 0);
        $cappedStock = min(99, max(0, $stock));

        $imageUrl = $global?->display_image_url ?? null;
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
        }

        return [
            'listing_id' => (int) $this->id,
            'global_product_id' => (int) $this->global_product_id,
            'name' => (string) ($global?->name ?? ''),
            'description' => $global?->description ?? null,
            'unit_type' => $global?->unit_type ?? null,
            'image' => $imageUrl,
            'price' => $price,
            'stock_quantity' => $cappedStock,
            'in_stock' => $stock > 0,
            'category' => $global?->category?->name ?? null,
            'is_favorite' => FavoriteLookup::isListingFavorite((int) $this->id),
            'shop' => [
                'id' => (int) $seller->id,
                'name' => (string) $seller->name,
                'opens_at' => $seller->opens_at,
                'closes_at' => $seller->closes_at,
                'is_open' => (bool) $seller->is_open,
                'accepting_orders' => (bool) $seller->isAcceptingOrders(),
                'is_favorite' => FavoriteLookup::isSellerFavorite((int) $seller->id),
            ],
        ];
    }
}
