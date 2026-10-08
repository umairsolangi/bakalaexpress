<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GlobalProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'catalog_category_id',
        'name',
        'description',
        'base_price',
        'unit_type',
        'default_image',
        'is_active',
        'created_by_admin_id',
        'updated_by_admin_id',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'effective_price',
        'display_image_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'catalog_category_id');
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'shop_product', 'global_product_id', 'seller_id')
            ->using(ShopProduct::class)
            ->withPivot(['id', 'custom_price', 'stock_quantity', 'is_active'])
            ->withTimestamps();
    }

    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class, 'global_product_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->pivot?->custom_price ?? $this->base_price);
    }

    public function getDisplayImageUrlAttribute(): string
    {
        if ($this->default_image) {
            if (Str::startsWith($this->default_image, ['http://', 'https://'])) {
                return $this->default_image;
            }
            if (file_exists(public_path('storage/' . $this->default_image))) {
                return asset('storage/' . $this->default_image);
            }
        }

        // Category-based high-quality placeholder image
        $categoryName = $this->category?->name ?? '';

        return match ($categoryName) {
            'Prepared Foods' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500&auto=format&fit=crop',
            'Dairy & Bakery' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=500&auto=format&fit=crop',
            'Hardware & Tools' => 'https://images.unsplash.com/photo-1581147036324-c17ac41dfa6c?q=80&w=500&auto=format&fit=crop',
            'Daily Essentials' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=500&auto=format&fit=crop',
            'Wholesale & Bakery' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?q=80&w=500&auto=format&fit=crop',
            'Fresh Produce' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=500&auto=format&fit=crop',
            'Wholesale & Snacks' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?q=80&w=500&auto=format&fit=crop',
            'Novelties & Gifts' => 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?q=80&w=500&auto=format&fit=crop',
            'Refreshments' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?q=80&w=500&auto=format&fit=crop',
            'Health & Pharmacy' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=500&auto=format&fit=crop',
            'Fresh Meat' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?q=80&w=500&auto=format&fit=crop',
            default => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=500&auto=format&fit=crop',
        };
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function updatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_admin_id');
    }
}
