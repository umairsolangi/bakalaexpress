<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'status',
        'documents',
        'business_description',
        'reason_for_verification',
        'rejection_reason',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'documents' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the seller that owns the verification request.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the admin who reviewed the verification request.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
