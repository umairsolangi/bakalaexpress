<?php

namespace App\Http\Resources\Api;

use App\Models\Seller;
use App\Support\Api\FavoriteLookup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public shop card resource for customer home and listings.
 *
 * Excludes sensitive fields (email, password, remember_token, document paths, etc.)
 */
class CustomerShopCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Seller $this */
        $profileImage = null;
        if (!empty($this->profile_image)) {
            $profileImage = (str_starts_with($this->profile_image, 'http://') || str_starts_with($this->profile_image, 'https://'))
                ? $this->profile_image
                : asset('storage/' . ltrim($this->profile_image, '/'));
        }

        // Calculate average rating and count on approved feedback
        $avgRating = round((float) ($this->approved_feedbacks_avg_rating ?? $this->approvedFeedbacks()->avg('rating') ?? 0), 1);
        $ratingCount = (int) ($this->approved_feedbacks_count ?? $this->approvedFeedbacks()->count() ?? 0);

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'profile_image' => $profileImage,
            'category' => $this->catalogCategory?->name ?? null,
            'area' => $this->area,
            'sector' => $this->sector,
            'near_areas' => is_array($this->near_areas) ? array_values($this->near_areas) : [],
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
            'is_open' => (bool) $this->is_open,
            'accepting_orders' => (bool) $this->isAcceptingOrders(),
            'average_rating' => $avgRating,
            'rating_count' => $ratingCount,
            'is_favorite' => FavoriteLookup::isSellerFavorite((int) $this->id),
        ];
    }
}
