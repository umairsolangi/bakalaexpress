<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ShopProduct extends Pivot
{
    protected $table = 'shop_product';

    public $incrementing = true;

    protected $fillable = [
        'seller_id',
        'global_product_id',
        'custom_price',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'effective_price',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function globalProduct(): BelongsTo
    {
        return $this->belongsTo(GlobalProduct::class, 'global_product_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->custom_price ?? $this->globalProduct?->base_price ?? 0);
    }
}
