<?php

namespace App\Http\Resources\Api;

use App\Models\GlobalProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerAvailableProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GlobalProduct $this */
        $category = $this->category;

        $imageUrl = $this->default_image;
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
        }

        return [
            'global_product_id' => (int) $this->id,
            'name' => (string) $this->name,
            'unit_type' => (string) ($this->unit_type ?? 'piece'),
            'image_url' => $imageUrl,
            'base_price' => number_format((float) $this->base_price, 2, '.', ''),
            'category' => [
                'id' => (int) ($category?->id ?? 0),
                'name' => (string) ($category?->name ?? 'Uncategorized'),
            ],
        ];
    }
}
