<?php

namespace App\Http\Resources\Api;

use App\Models\ShopProduct;
use App\Support\Api\FavoriteLookup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public catalog product listing resource for customer views.
 */
class CustomerProductListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopProduct $this */
        $global = $this->globalProduct;
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
        ];
    }
}
