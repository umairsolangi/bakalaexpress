<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends Model
{
    use HasFactory;

    protected $table = 'device_tokens';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'token',
        'platform',
        'device_name',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the owning model (User, Seller, or Rider).
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
