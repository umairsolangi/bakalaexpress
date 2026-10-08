<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shop_product_id',
        'global_product_id',
        'product_id',
        'item_name',
        'unit_type',
        'item_image',
        'quantity',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shopProduct()
    {
        return $this->belongsTo(ShopProduct::class, 'shop_product_id');
    }

    public function globalProduct()
    {
        return $this->belongsTo(GlobalProduct::class, 'global_product_id');
    }

    // Define relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
