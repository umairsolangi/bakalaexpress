<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Seller extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'city',
        'area',
        'sector',
        'near_areas',
        'full_address',
        'opens_at',
        'closes_at',
        'is_open',
        'catalog_category_id',
        'accountIsApproved',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'near_areas' => 'array',
        'is_open' => 'boolean',
    ];

    // Mutator to hash passwords only if they aren't already hashed
    public function setPasswordAttribute($password)
    {
        if (Hash::needsRehash($password)) {
            $this->attributes['password'] = Hash::make($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function catalogCategory(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'catalog_category_id');
    }

    public function catalogProducts(): BelongsToMany
    {
        return $this->belongsToMany(GlobalProduct::class, 'shop_product', 'seller_id', 'global_product_id')
            ->using(ShopProduct::class)
            ->withPivot(['id', 'custom_price', 'stock_quantity', 'is_active'])
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'seller_id');
    }

    public function approvedFeedbacks()
    {
        return $this->hasMany(Feedback::class, 'seller_id')->where('status', 'approved');
    }

    public function isAcceptingOrders(): bool
    {
        if (!$this->is_open) {
            return false;
        }

        if (!$this->opens_at || !$this->closes_at) {
            return true;
        }

        $now = now()->format('H:i:s');
        return $this->opens_at <= $this->closes_at
            ? $now >= $this->opens_at && $now <= $this->closes_at
            : $now >= $this->opens_at || $now <= $this->closes_at;
    }

    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable');
    }

    public function verificationRequest()
    {
        return $this->hasOne(SellerVerification::class);
    }

    public function isVerified()
    {
        return $this->verificationRequest && $this->verificationRequest->status === 'approved';
    }

    /**
     * Category-specific visual profile helper
     */
    public function getCategoryProfileDetails(): array
    {
        $catName = $this->catalogCategory?->name ?? 'General Store';

        return match ($catName) {
            'Prepared Foods' => [
                'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-utensils',
                'gradient' => 'from-amber-950 via-orange-900 to-neutral-950',
                'tagline' => 'Fresh Prepared Meals, Fried Snacks & Fast Food',
            ],
            'Dairy & Bakery' => [
                'image' => 'https://images.unsplash.com/photo-1628102491629-778571d893a3?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-cow',
                'gradient' => 'from-blue-950 via-sky-900 to-slate-900',
                'tagline' => 'Fresh Milk, Yogurt, Bakery Goods & Dairy Products',
            ],
            'Hardware & Tools' => [
                'image' => 'https://images.unsplash.com/photo-1581147036324-c17ac41dfa6c?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-wrench',
                'gradient' => 'from-slate-950 via-zinc-900 to-neutral-900',
                'tagline' => 'Home Repair Tools, Electrical & Hardware Accessories',
            ],
            'Daily Essentials' => [
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-fire',
                'gradient' => 'from-orange-950 via-amber-900 to-neutral-950',
                'tagline' => 'Fresh Tandoori Flatbreads & Daily Household Provisions',
            ],
            'Wholesale & Bakery' => [
                'image' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-egg',
                'gradient' => 'from-amber-950 via-yellow-900 to-slate-900',
                'tagline' => 'Wholesale Poultry Eggs & Commercial Baking Goods',
            ],
            'Fresh Produce' => [
                'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-apple-whole',
                'gradient' => 'from-emerald-950 via-green-900 to-slate-950',
                'tagline' => 'Farm-Fresh Vegetables, Leafy Greens & Seasonal Fruits',
            ],
            'Wholesale & Snacks' => [
                'image' => 'https://images.unsplash.com/photo-1581798459219-318e76aecc7b?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-candy-cane',
                'gradient' => 'from-purple-950 via-fuchsia-900 to-slate-900',
                'tagline' => 'Bulk Sweets, Candies & Wholesale Packaged Snacks',
            ],
            'Novelties & Gifts' => [
                'image' => 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-gift',
                'gradient' => 'from-rose-950 via-pink-900 to-slate-900',
                'tagline' => 'Toys, Packaging Materials & Novelty Gift Items',
            ],
            'Refreshments' => [
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-glass-water',
                'gradient' => 'from-teal-950 via-cyan-900 to-slate-950',
                'tagline' => 'Traditional Paan, Chilled Beverages & Desserts',
            ],
            'Health & Pharmacy' => [
                'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-clinic-medical',
                'gradient' => 'from-rose-950 via-red-900 to-slate-950',
                'tagline' => 'OTC Medicines & Personal Healthcare Products',
            ],
            'Fresh Meat' => [
                'image' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-drumstick-bite',
                'gradient' => 'from-red-950 via-rose-900 to-neutral-950',
                'tagline' => 'Fresh Processed Poultry & Custom Raw Meat Cuts',
            ],
            default => [
                'image' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?q=80&w=400&auto=format&fit=crop',
                'icon' => 'fa-basket-shopping',
                'gradient' => 'from-emerald-950 via-teal-900 to-slate-900',
                'tagline' => 'Daily Essentials, Pantry Staples & Household Provisions',
            ],
        };
    }

    public function getDisplayProfileImageAttribute(): string
    {
        if ($this->profile_image && file_exists(public_path('storage/' . $this->profile_image))) {
            return asset('storage/' . $this->profile_image);
        }

        return $this->getCategoryProfileDetails()['image'];
    }
}
