<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isUsableFor(float $subtotal): bool
    {
        return $this->is_active
            && $subtotal >= (float) $this->minimum_order_amount
            && (!$this->starts_at || $this->starts_at->isPast())
            && (!$this->expires_at || $this->expires_at->isFuture())
            && (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->discount_type === 'percent'
            ? ($subtotal * (float) $this->discount_value) / 100
            : (float) $this->discount_value;

        if ($this->maximum_discount_amount !== null) {
            $discount = min($discount, (float) $this->maximum_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
