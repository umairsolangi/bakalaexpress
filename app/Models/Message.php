<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'order_id', 'sender_id', 'sender_type', 'message', 'is_read'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
