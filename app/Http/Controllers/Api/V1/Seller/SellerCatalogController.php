<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Exceptions\Api\CatalogException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SellerAvailableCatalogRequest;
use App\Http\Requests\Api\SellerBulkPriceRequest;
use App\Http\Requests\Api\SellerCatalogImportRequest;
use App\Http\Requests\Api\SellerCatalogListRequest;
use App\Http\Requests\Api\SellerListingUpdateRequest;
use App\Http\Resources\Api\SellerAvailableProductResource;
use App\Http\Resources\Api\SellerListingResource;
use App\Models\Seller;
use App\Services\Api\ApiResponse;
use App\Services\Api\SellerCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerCatalogController extends Controller
{
    public function __construct(
        protected SellerCatalogService $catalogService
    ) {}

    protected function seller(Request $request): Seller
    {
        return $request->user();
    }

    /**
     * 1. GET /api/v1/seller/catalog/listings
     */
    public function index(SellerCatalogListRequest $request): JsonResponse
    {
        $seller = $this->seller($request);
        $result = $this->catalogService->getListings($seller, $request->validated());

        return ApiResponse::success(
            SellerListingResource::collection($result['paginator']->items()),
            'Catalog listings retrieved successfully.',
            $result['meta']
        );
    }

    /**
     * 2. GET /api/v1/seller/catalog/available
     */
    public function available(SellerAvailableCatalogRequest $request): JsonResponse
    {
        $seller = $this->seller($request);
        $paginator = $this->catalogService->getAvailableProducts($seller, $request->validated());

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'server_time' => now()->toIso8601String(),
        ];

        return ApiResponse::success(
            SellerAvailableProductResource::collection($paginator->items()),
            'Available catalog products retrieved successfully.',
            $meta
        );
    }

    /**
     * 3. POST /api/v1/seller/catalog/import
     */
    public function import(SellerCatalogImportRequest $request): JsonResponse
    {
        $seller = $this->seller($request);
        try {
            $summary = $this->catalogService->importProducts($seller, $request->validated());

            return ApiResponse::success(
                $summary,
                "{$summary['imported_count']} products imported successfully."
            );
        } catch (CatalogException $e) {
            return ApiResponse::error($e->getMessage(), $e->getErrorCode(), [], $e->getStatusCode());
        }
    }

    /**
     * 4. PUT /api/v1/seller/catalog/listings/{listing}
     */
    public function update(SellerListingUpdateRequest $request, int $listing): JsonResponse
    {
        $seller = $this->seller($request);
        try {
            $updatedListing = $this->catalogService->updateListing($seller, $listing, $request->validated());

            return ApiResponse::success(
                new SellerListingResource($updatedListing),
                'Listing updated successfully.'
            );
        } catch (CatalogException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                $e->getErrorCode(),
                [],
                $e->getStatusCode()
            );
        }
    }

    /**
     * 5. POST /api/v1/seller/catalog/bulk-price
     */
    public function bulkPrice(SellerBulkPriceRequest $request): JsonResponse
    {
        $seller = $this->seller($request);
        try {
            $result = $this->catalogService->bulkPrice($seller, $request->validated());

            $message = !empty($result['preview'])
                ? 'Bulk price preview calculated successfully.'
                : "Bulk price update complete: {$result['updated_count']} updated, {$result['skipped_count']} skipped.";

            return ApiResponse::success($result, $message);
        } catch (CatalogException $e) {
            return ApiResponse::error($e->getMessage(), $e->getErrorCode(), [], $e->getStatusCode());
        }
    }
}
