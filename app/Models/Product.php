<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'name',
        'description',
        'seller_city',
        'seller_area',
        'seller_contact_no',
        'price',
        'stock_quantity',
        'unit_type',
        'category_id',
        'image',
        'is_approved',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

}
