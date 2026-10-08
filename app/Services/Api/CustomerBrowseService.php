<?php

namespace App\Services\Api;

use App\Http\Resources\Api\CustomerProductListingResource;
use App\Http\Resources\Api\CustomerShopCardResource;
use App\Models\CatalogCategory;
use App\Models\Feedback;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Support\Api\LocationOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service orchestrating read-only customer browsing workflows.
 *
 * Re-implements business rules from web controllers without session, redirect, or view dependencies:
 * - LocationOptions duplicates hardcoded arrays from App\Http\Controllers\HomeController@showHomePage (lines 59-68).
 * - Shop eligibility and filters copy App\Http\Controllers\HomeController@showHomePage (lines 39-58) and HomeController@index (line 92).
 * - Storefront catalog listings and category grouping copy App\Http\Controllers\ProductController@showSellerProducts (lines 51-76).
 * - Single listing detail verification copies App\Http\Controllers\ProductController@showCatalogProduct (lines 24-49).
 * - Approved reviews copy App\Http\Controllers\ProductController@showSellerProducts (lines 69-74).
 * - Search query logic and wildcard handling copy and harden App\Http\Controllers\ProductController@searchProducts (lines 78-123).
 */
class CustomerBrowseService
{
    /**
     * Escape SQL LIKE wildcards (% and _) using standard SQL ESCAPE '!' to treat user inputs as plain characters.
     */
    public static function escapeLikeWildcards(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    /**
     * Retrieve static locations and active categories.
     * Copied from: App\Http\Controllers\HomeController@showHomePage (lines 37, 59-68)
     */
    public function getLocationsAndCategories(): array
    {
        $categories = CatalogCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return [
            'sectors' => LocationOptions::sectors(),
            'near_areas' => LocationOptions::nearAreas(),
            'categories' => $categories,
        ];
    }

    /**
     * Retrieve paginated eligible shops for the homepage with filters.
     * Copied from: App\Http\Controllers\HomeController@showHomePage (lines 39-58)
     */
    public function getHomeShops(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = Seller::query()
            ->where('accountIsApproved', 1)
            ->where('is_deleted', false)
            ->with(['catalogCategory'])
            ->withAvg('approvedFeedbacks', 'rating')
            ->withCount('approvedFeedbacks')
            ->orderBy('id', 'asc');

        if (!empty($filters['sector'])) {
            $query->where('sector', $filters['sector']);
        }

        if (!empty($filters['near_area'])) {
            $query->whereJsonContains('near_areas', $filters['near_area']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('catalog_category_id', (int) $filters['category_id']);
        }

        if (isset($filters['is_open']) && $filters['is_open'] !== null) {
            $query->where('is_open', (bool) $filters['is_open']);
        }

        if (!empty($filters['q'])) {
            $escaped = self::escapeLikeWildcards(trim((string) $filters['q']));
            $query->whereRaw("name LIKE ? ESCAPE '!'", ["%{$escaped}%"]);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find an eligible seller or return null.
     */
    public function findEligibleSeller(int $sellerId): ?Seller
    {
        return Seller::query()
            ->where('id', $sellerId)
            ->where('accountIsApproved', 1)
            ->where('is_deleted', false)
            ->with(['catalogCategory'])
            ->withAvg('approvedFeedbacks', 'rating')
            ->withCount('approvedFeedbacks')
            ->first();
    }

    /**
     * Retrieve seller profile and active catalog listings grouped by category.
     * Copied from: App\Http\Controllers\ProductController@showSellerProducts (lines 51-76)
     */
    public function getSellerProfileAndCatalog(int $sellerId): ?array
    {
        $seller = $this->findEligibleSeller($sellerId);
        if (!$seller) {
            return null;
        }

        // Fetch active catalog listings eager loading global product and category (avoids N+1)
        $listings = ShopProduct::query()
            ->with(['globalProduct.category'])
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->whereHas('globalProduct', function ($q) {
                $q->where('is_active', true);
            })
            ->get();

        // Group listings by category in PHP memory
        $categoryGroups = [];
        foreach ($listings as $listing) {
            $global = $listing->globalProduct;
            $catId = (int) ($global?->catalog_category_id ?? 0);
            $catName = $global?->category?->name ?? 'General Store';
            $catSlug = $global?->category?->slug ?? 'general-store';

            if (!isset($categoryGroups[$catId])) {
                $categoryGroups[$catId] = [
                    'id' => $catId,
                    'name' => $catName,
                    'slug' => $catSlug,
                    'products' => [],
                ];
            }

            $categoryGroups[$catId]['products'][] = (new CustomerProductListingResource($listing))->resolve();
        }

        return [
            'seller' => (new CustomerShopCardResource($seller))->resolve(),
            'categories' => array_values($categoryGroups),
        ];
    }

    /**
     * Retrieve single listing detail with shop summary.
     * Copied from: App\Http\Controllers\ProductController@showCatalogProduct (lines 24-49)
     */
    public function getListingDetail(int $sellerId, int $listingId): ?ShopProduct
    {
        $seller = $this->findEligibleSeller($sellerId);
        if (!$seller) {
            return null;
        }

        return ShopProduct::query()
            ->with(['seller', 'globalProduct.category'])
            ->where('id', $listingId)
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->whereHas('globalProduct', function ($q) {
                $q->where('is_active', true);
            })
            ->first();
    }

    /**
     * Retrieve paginated approved feedback for an eligible seller.
     * Copied from: App\Http\Controllers\ProductController@showSellerProducts (lines 69-74)
     */
    public function getSellerReviews(int $sellerId, int $page = 1, int $perPage = 15): ?LengthAwarePaginator
    {
        $seller = $this->findEligibleSeller($sellerId);
        if (!$seller) {
            return null;
        }

        return Feedback::query()
            ->with(['user:id,name'])
            ->where('seller_id', $seller->id)
            ->whereHas('user')
            ->where('status', 'approved')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Search shops and active catalog listings.
     * Copied and hardened from: App\Http\Controllers\ProductController@searchProducts (lines 78-123)
     */
    public function search(
        string $keyword,
        ?string $sector = null,
        string $type = 'all',
        int $page = 1,
        int $perPage = 15,
        array $filters = []
    ): array {
        $escaped = self::escapeLikeWildcards(trim($keyword));
        $result = [
            'type' => $type,
            'shops' => [],
            'products' => [],
        ];

        // Search shops
        if ($type === 'shops' || $type === 'all') {
            $shopsQuery = Seller::query()
                ->where('accountIsApproved', 1)
                ->where('is_deleted', false)
                ->with(['catalogCategory'])
                ->withAvg('approvedFeedbacks', 'rating')
                ->withCount('approvedFeedbacks')
                ->whereRaw("name LIKE ? ESCAPE '!'", ["%{$escaped}%"]);

            if (!empty($sector)) {
                $shopsQuery->where('sector', $sector);
            }

            if (!empty($filters['category_id'])) {
                $shopsQuery->where('catalog_category_id', (int) $filters['category_id']);
            }

            if ($type === 'shops') {
                $paginatedShops = $shopsQuery->paginate($perPage, ['*'], 'page', $page);
                return [
                    'paginated' => $paginatedShops,
                    'is_paginated' => true,
                ];
            }

            $result['shops'] = CustomerShopCardResource::collection($shopsQuery->take(15)->get())->resolve();
        }

        // Search products (matching both name and description like webapp ProductController@searchProducts)
        if ($type === 'products' || $type === 'all') {
            $productsQuery = ShopProduct::query()
                ->with(['seller', 'globalProduct.category'])
                ->where('is_active', true)
                ->whereHas('seller', function ($sq) use ($sector) {
                    $sq->where('accountIsApproved', 1)
                        ->where('is_deleted', false);
                    if (!empty($sector)) {
                        $sq->where('sector', $sector);
                    }
                })
                ->whereHas('globalProduct', function ($gq) use ($escaped, $filters) {
                    $gq->where('is_active', true)
                        ->where(function ($queryBuilder) use ($escaped) {
                            $queryBuilder->whereRaw("name LIKE ? ESCAPE '!'", ["%{$escaped}%"])
                                ->orWhereRaw("description LIKE ? ESCAPE '!'", ["%{$escaped}%"]);
                        });

                    if (!empty($filters['category_id'])) {
                        $gq->where('catalog_category_id', (int) $filters['category_id']);
                    }
                });

            if (!empty($filters['seller_id'])) {
                $productsQuery->where('seller_id', (int) $filters['seller_id']);
            }

            if (!empty($filters['in_stock'])) {
                $productsQuery->where('stock_quantity', '>', 0);
            }

            if ($type === 'products') {
                $paginatedProducts = $productsQuery->paginate($perPage, ['*'], 'page', $page);
                return [
                    'paginated' => $paginatedProducts,
                    'is_paginated' => true,
                ];
            }

            $products = $productsQuery->take(15)->get()->map(function (ShopProduct $listing) {
                return $this->formatSearchResultProduct($listing);
            })->values()->all();

            $result['products'] = $products;
        }

        return [
            'data' => $result,
            'is_paginated' => false,
        ];
    }

    /**
     * Format a product search result item.
     */
    public function formatSearchResultProduct(ShopProduct $listing): array
    {
        $global = $listing->globalProduct;
        $seller = $listing->seller;

        $imageUrl = $global?->display_image_url ?? null;
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
        }

        return [
            'listing_id' => (int) $listing->id,
            'seller_id' => (int) $listing->seller_id,
            'shop_name' => (string) ($seller?->shop_name ?: $seller?->name ?? ''),
            'product_name' => (string) ($global?->name ?? ''),
            'price' => (float) ($listing->custom_price ?? $global?->base_price ?? 0),
            'image' => $imageUrl,
            'in_stock' => (int) ($listing->stock_quantity ?? 0) > 0,
            'shop_accepting_orders' => (bool) ($seller?->isAcceptingOrders() ?? false),
        ];
    }
}
