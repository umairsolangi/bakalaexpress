<?php

namespace App\Observers;

use App\Models\GlobalProduct;
use App\Models\Seller;

class SellerObserver
{
    public function created(Seller $seller): void
    {
        if (!$seller->catalog_category_id) {
            return;
        }

        $productIds = GlobalProduct::query()
            ->where('catalog_category_id', $seller->catalog_category_id)
            ->where('is_active', true)
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        $attachData = $productIds->mapWithKeys(function ($productId) {
            return [
                $productId => [
                    'custom_price' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
        })->all();

        $seller->catalogProducts()->syncWithoutDetaching($attachData);
    }
}
