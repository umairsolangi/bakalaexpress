<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback'; // Explicitly define the table name

    protected $fillable = ['order_id', 'user_id', 'seller_id', 'feedback', 'rating', 'status', 'moderated_at', 'moderated_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Customer who gave feedback
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id'); // Seller receiving feedback
    }
     // Relationship with the order
     public function order()
     {
         return $this->belongsTo(Order::class, 'order_id');
     }
}
