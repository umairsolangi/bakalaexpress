<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'created_by_admin_id',
        'updated_by_admin_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class, 'catalog_category_id');
    }

    public function globalProducts(): HasMany
    {
        return $this->hasMany(GlobalProduct::class, 'catalog_category_id');
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
