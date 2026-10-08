<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerProductListingResource;
use App\Http\Resources\Api\CustomerShopCardResource;
use App\Models\Favorite;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Services\Api\ApiResponse;
use App\Support\Api\FavoriteLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFavoriteController extends Controller
{
    /**
     * GET /api/v1/customer/favorites/sellers
     * Return paginated favorite shops for the logged-in customer.
     */
    public function favoriteSellers(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $perPage = min(30, max(1, $request->integer('per_page', 15)));

        $paginator = Seller::query()
            ->whereIn('id', function ($query) use ($customer) {
                $query->select('seller_id')
                    ->from('favorites')
                    ->where('user_id', $customer->id)
                    ->whereNotNull('seller_id');
            })
            ->where('is_deleted', false)
            ->where('accountIsApproved', 1)
            ->with(['catalogCategory'])
            ->withAvg('approvedFeedbacks', 'rating')
            ->withCount('approvedFeedbacks')
            ->latest()
            ->paginate($perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return ApiResponse::success(
            CustomerShopCardResource::collection($paginator->items()),
            'Favorite sellers retrieved successfully.',
            $meta
        );
    }

    /**
     * GET /api/v1/customer/favorites/products
     * Return paginated favorite products for the logged-in customer.
     */
    public function favoriteProducts(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $perPage = min(30, max(1, $request->integer('per_page', 15)));

        $paginator = ShopProduct::query()
            ->whereIn('id', function ($query) use ($customer) {
                $query->select('shop_product_id')
                    ->from('favorites')
                    ->where('user_id', $customer->id)
                    ->whereNotNull('shop_product_id');
            })
            ->where('is_active', true)
            ->with(['seller', 'globalProduct.category'])
            ->latest()
            ->paginate($perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return ApiResponse::success(
            CustomerProductListingResource::collection($paginator->items()),
            'Favorite products retrieved successfully.',
            $meta
        );
    }

    /**
     * POST /api/v1/customer/favorites/sellers/{seller}/toggle
     */
    public function toggleSeller(Request $request, int $sellerId): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $seller = Seller::where('is_deleted', false)->where('accountIsApproved', 1)->find($sellerId);

        if (!$seller) {
            return ApiResponse::error('Seller not found.', 'NOT_FOUND', [], 404);
        }

        $favorite = Favorite::where('user_id', $customer->id)
            ->where('seller_id', $seller->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            FavoriteLookup::reset();
            return ApiResponse::success(['is_favorite' => false], 'Seller removed from favorites.');
        }

        Favorite::create([
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        FavoriteLookup::reset();
        return ApiResponse::success(['is_favorite' => true], 'Seller added to favorites.');
    }

    /**
     * POST /api/v1/customer/favorites/products/{listing}/toggle
     */
    public function toggleProduct(Request $request, int $listingId): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $listing = ShopProduct::where('is_active', true)->find($listingId);

        if (!$listing) {
            return ApiResponse::error('Product not found.', 'NOT_FOUND', [], 404);
        }

        $favorite = Favorite::where('user_id', $customer->id)
            ->where('shop_product_id', $listing->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            FavoriteLookup::reset();
            return ApiResponse::success(['is_favorite' => false], 'Product removed from favorites.');
        }

        Favorite::create([
            'user_id' => $customer->id,
            'shop_product_id' => $listing->id,
        ]);

        FavoriteLookup::reset();
        return ApiResponse::success(['is_favorite' => true], 'Product added to favorites.');
    }
}
