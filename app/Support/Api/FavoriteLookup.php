<?php

namespace App\Support\Api;

use App\Models\Favorite;
use App\Models\User;

class FavoriteLookup
{
    protected static ?array $favoriteSellerIds = null;
    protected static ?array $favoriteListingIds = null;
    protected static ?int $loadedUserId = null;

    public static function isSellerFavorite(int $sellerId): bool
    {
        self::ensureLoaded();
        return in_array($sellerId, self::$favoriteSellerIds ?? [], true);
    }

    public static function isListingFavorite(int $listingId): bool
    {
        self::ensureLoaded();
        return in_array($listingId, self::$favoriteListingIds ?? [], true);
    }

    protected static function ensureLoaded(): void
    {
        $user = auth('sanctum')->user();
        $userId = ($user instanceof User) ? $user->id : null;

        if (self::$loadedUserId !== $userId || self::$favoriteSellerIds === null) {
            self::$loadedUserId = $userId;
            if ($userId) {
                $favorites = Favorite::where('user_id', $userId)->get(['seller_id', 'shop_product_id']);

                self::$favoriteSellerIds = $favorites->pluck('seller_id')
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all();

                self::$favoriteListingIds = $favorites->pluck('shop_product_id')
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all();
            } else {
                self::$favoriteSellerIds = [];
                self::$favoriteListingIds = [];
            }
        }
    }

    public static function reset(): void
    {
        self::$favoriteSellerIds = null;
        self::$favoriteListingIds = null;
        self::$loadedUserId = null;
    }
}
