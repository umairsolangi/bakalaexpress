<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomerHomeRequest;
use App\Http\Requests\Api\CustomerSearchRequest;
use App\Http\Requests\Api\PaginationRequest;
use App\Http\Resources\Api\CustomerProductDetailResource;
use App\Http\Resources\Api\CustomerReviewResource;
use App\Http\Resources\Api\CustomerShopCardResource;
use App\Services\Api\ApiResponse;
use App\Services\Api\CustomerBrowseService;
use Illuminate\Http\JsonResponse;

class CustomerBrowseController extends Controller
{
    public function __construct(
        protected CustomerBrowseService $browseService
    ) {}

    /**
     * 1. GET /api/v1/customer/meta/locations
     * Return hardcoded sectors, near areas, and active catalog categories.
     */
    public function locations(): JsonResponse
    {
        $data = $this->browseService->getLocationsAndCategories();

        return ApiResponse::success($data, 'Locations and categories retrieved successfully.');
    }

    /**
     * 2. GET /api/v1/customer/home
     * Return paginated eligible shops for home browsing.
     */
    public function home(CustomerHomeRequest $request): JsonResponse
    {
        $page = $request->integer('page', 1);
        $perPage = min(30, max(1, $request->integer('per_page', 15)));
        $categoryId = $request->input('category_id') ?? $request->input('category');
        $filters = [
            'sector' => $request->input('sector'),
            'near_area' => $request->input('near_area'),
            'category_id' => $categoryId ? (int) $categoryId : null,
            'q' => $request->input('q'),
            'is_open' => $request->has('is_open') ? $request->boolean('is_open') : null,
        ];

        $paginator = $this->browseService->getHomeShops($filters, $page, $perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return ApiResponse::success(
            CustomerShopCardResource::collection($paginator->items()),
            'Shops retrieved successfully.',
            $meta
        );
    }

    /**
     * 3. GET /api/v1/customer/sellers/{seller}
     * Return public shop profile and active catalog listings grouped by category.
     */
    public function sellerDetail(int $seller): JsonResponse
    {
        $data = $this->browseService->getSellerProfileAndCatalog($seller);

        if (!$data) {
            return ApiResponse::error('Seller not found.', 'NOT_FOUND', [], 404);
        }

        return ApiResponse::success($data, 'Seller profile and catalog retrieved successfully.');
    }

    /**
     * 4. GET /api/v1/customer/sellers/{seller}/products/{listing}
     * Return single catalog listing details with shop summary.
     */
    public function productDetail(int $seller, int $listing): JsonResponse
    {
        $listingItem = $this->browseService->getListingDetail($seller, $listing);

        if (!$listingItem) {
            return ApiResponse::error('Product not found.', 'NOT_FOUND', [], 404);
        }

        return ApiResponse::success(
            new CustomerProductDetailResource($listingItem),
            'Product details retrieved successfully.'
        );
    }

    /**
     * 5. GET /api/v1/customer/sellers/{seller}/reviews
     * Return paginated approved feedback with masked reviewer identities.
     */
    public function reviews(PaginationRequest $request, int $seller): JsonResponse
    {
        $page = $request->integer('page', 1);
        $perPage = min(30, max(1, $request->integer('per_page', 15)));

        $paginator = $this->browseService->getSellerReviews($seller, $page, $perPage);

        if (!$paginator) {
            return ApiResponse::error('Seller not found.', 'NOT_FOUND', [], 404);
        }

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return ApiResponse::success(
            CustomerReviewResource::collection($paginator->items()),
            'Reviews retrieved successfully.',
            $meta
        );
    }

    /**
     * 6. GET /api/v1/customer/search
     * Search shops and active catalog listings by keyword.
     */
    public function search(CustomerSearchRequest $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $sector = $request->filled('sector') ? trim((string) $request->input('sector')) : null;
        $type = $request->input('type', 'all');
        $page = $request->integer('page', 1);
        $perPage = min(30, max(1, $request->integer('per_page', 15)));
        $categoryId = $request->input('category_id') ?? $request->input('category');
        $filters = [
            'category_id' => $categoryId ? (int) $categoryId : null,
            'in_stock' => $request->has('in_stock') ? $request->boolean('in_stock') : null,
            'seller_id' => $request->filled('seller_id') ? $request->integer('seller_id') : null,
        ];

        $searchResult = $this->browseService->search($q, $sector, $type, $page, $perPage, $filters);

        if ($searchResult['is_paginated'] ?? false) {
            $paginator = $searchResult['paginated'];
            $meta = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];

            if ($type === 'shops') {
                return ApiResponse::success(
                    [
                        'type' => 'shops',
                        'shops' => CustomerShopCardResource::collection($paginator->items()),
                    ],
                    'Search results retrieved successfully.',
                    $meta
                );
            }

            $products = collect($paginator->items())
                ->map(fn($item) => $this->browseService->formatSearchResultProduct($item))
                ->values()
                ->all();

            return ApiResponse::success(
                [
                    'type' => 'products',
                    'products' => $products,
                ],
                'Search results retrieved successfully.',
                $meta
            );
        }

        return ApiResponse::success(
            $searchResult['data'],
            'Search results retrieved successfully.'
        );
    }
}
