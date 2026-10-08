<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\SellerController;


class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'address',
        'phone',
        'status',
        'total_amount',
        'transaction_id'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedbacks()
    {
        return $this->hasOne(Feedback::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    /**
     * Get earnings for a seller in a specific time period
     * 
     * @param int $sellerId
     * @param string $period (all, week, month, year)
     * @return array Returns an array with total and count of orders
     */
    public static function getSellerEarnings($sellerId, $period = 'all')
    {
        $query = self::where('seller_id', $sellerId)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhere('status', 'delivered');
            });

        // Apply date filtering based on period
        if ($period == 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period == 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period == 'year') {
            $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        }

        $orders = $query->get();

        return [
            'total' => $orders->sum('total_amount'),
            'count' => $orders->count()
        ];
    }

}
