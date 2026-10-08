<?php

namespace App\Services\Api;

use App\Exceptions\Api\CatalogException;
use App\Models\GlobalProduct;
use App\Models\Seller;
use App\Models\ShopProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SellerCatalogService
{
    /**
     * Get paginated listings for the seller with filters and meta counts.
     */
    public function getListings(Seller $seller, array $filters = []): array
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min(30, $perPage));
        $threshold = (int) config('bakala_orders.catalog_low_stock_threshold', 5);

        $query = ShopProduct::query()
            ->with(['globalProduct.category'])
            ->where('shop_product.seller_id', $seller->id);

        // Search by product name (with escaped wildcards)
        if (!empty($filters['q'])) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($filters['q']));
            $query->whereHas('globalProduct', function ($q) use ($escaped) {
                $q->where('name', 'like', '%' . $escaped . '%');
            });
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $catId = (int) $filters['category_id'];
            $query->whereHas('globalProduct', function ($q) use ($catId) {
                $q->where('catalog_category_id', $catId);
            });
        }

        // Active status filter
        if (isset($filters['is_active']) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('shop_product.is_active', (bool) $filters['is_active']);
        }

        // Stock status filter: low, out, in
        if (!empty($filters['stock'])) {
            $stock = (string) $filters['stock'];
            if ($stock === 'low') {
                $query->where('shop_product.stock_quantity', '>', 0)
                      ->where('shop_product.stock_quantity', '<=', $threshold);
            } elseif ($stock === 'out') {
                $query->where('shop_product.stock_quantity', '<=', 0);
            } elseif ($stock === 'in') {
                $query->where('shop_product.stock_quantity', '>', 0);
            }
        }

        // Sorting
        $sort = $filters['sort'] ?? 'updated_at';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sort === 'name') {
            $query->join('global_products', 'shop_product.global_product_id', '=', 'global_products.id')
                  ->orderBy('global_products.name', $direction)
                  ->select('shop_product.*');
        } elseif ($sort === 'price') {
            $query->join('global_products', 'shop_product.global_product_id', '=', 'global_products.id')
                  ->orderByRaw('COALESCE(shop_product.custom_price, global_products.base_price) ' . $direction)
                  ->select('shop_product.*');
        } elseif ($sort === 'stock') {
            $query->orderBy('shop_product.stock_quantity', $direction);
        } else {
            $query->orderBy('shop_product.updated_at', $direction);
        }

        $paginator = $query->paginate($perPage);

        // Aggregate meta counts for seller inventory in a single query
        $counts = ShopProduct::where('seller_id', $seller->id)
            ->selectRaw("
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as total_active,
                COUNT(CASE WHEN is_active = 0 THEN 1 END) as total_inactive,
                COUNT(CASE WHEN stock_quantity > 0 AND stock_quantity <= ? THEN 1 END) as total_low_stock,
                COUNT(CASE WHEN stock_quantity <= 0 THEN 1 END) as total_out_of_stock
            ", [$threshold])
            ->first();

        return [
            'paginator' => $paginator,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_active' => (int) ($counts->total_active ?? 0),
                'total_inactive' => (int) ($counts->total_inactive ?? 0),
                'total_low_stock' => (int) ($counts->total_low_stock ?? 0),
                'total_out_of_stock' => (int) ($counts->total_out_of_stock ?? 0),
                'server_time' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get master catalog products available to be imported by the seller.
     */
    public function getAvailableProducts(Seller $seller, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min(30, $perPage));

        if (!$seller->catalog_category_id) {
            return GlobalProduct::whereRaw('1 = 0')->paginate($perPage);
        }

        $existingProductIds = ShopProduct::where('seller_id', $seller->id)->pluck('global_product_id');

        $query = GlobalProduct::with('category')
            ->where('catalog_category_id', $seller->catalog_category_id)
            ->where('is_active', true)
            ->whereNotIn('id', $existingProductIds);

        if (!empty($filters['q'])) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($filters['q']));
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    /**
     * Import products from master catalog into seller's inventory.
     */
    public function importProducts(Seller $seller, array $data): array
    {
        if (!$seller->catalog_category_id) {
            throw new CatalogException('Please assign a shop category before importing products.', 'SELLER_CATEGORY_MISSING', 422);
        }

        return DB::transaction(function () use ($seller, $data) {
            $existingIds = ShopProduct::where('seller_id', $seller->id)
                ->pluck('global_product_id')
                ->all();

            $importAll = !empty($data['import_all_category']);
            $targetIds = $importAll ? null : array_map('intval', (array) ($data['global_product_ids'] ?? []));

            $candidatesQuery = GlobalProduct::where('catalog_category_id', $seller->catalog_category_id);
            if (!$importAll) {
                $candidatesQuery->whereIn('id', $targetIds);
            }
            $candidates = $candidatesQuery->get();

            $importedIds = [];
            $skippedExistingIds = [];
            $skippedUnavailableIds = [];

            $now = now();
            $attachRows = [];

            // If specific IDs were requested, check for ones missing or in different category
            if (!$importAll && $targetIds) {
                $candidateIds = $candidates->pluck('id')->all();
                foreach ($targetIds as $tid) {
                    if (!in_array($tid, $candidateIds, true)) {
                        $skippedUnavailableIds[] = $tid;
                    }
                }
            }

            foreach ($candidates as $product) {
                $pid = (int) $product->id;

                if (in_array($pid, $existingIds, true)) {
                    $skippedExistingIds[] = $pid;
                    continue;
                }

                if (!$product->is_active || $product->trashed()) {
                    $skippedUnavailableIds[] = $pid;
                    continue;
                }

                $importedIds[] = $pid;
                $attachRows[] = [
                    'seller_id' => $seller->id,
                    'global_product_id' => $pid,
                    'custom_price' => null,
                    'stock_quantity' => 0,
                    'is_active' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($attachRows)) {
                DB::table('shop_product')->insert($attachRows);
            }

            return [
                'imported' => count($importedIds),
                'imported_count' => count($importedIds),
                'imported_ids' => $importedIds,
                'skipped_existing' => count($skippedExistingIds),
                'skipped_existing_count' => count($skippedExistingIds),
                'skipped_existing_ids' => $skippedExistingIds,
                'skipped_unavailable' => count($skippedUnavailableIds),
                'skipped_unavailable_count' => count($skippedUnavailableIds),
                'skipped_unavailable_ids' => $skippedUnavailableIds,
            ];
        });
    }

    /**
     * Update listing pricing, stock, or status with row locking.
     */
    public function updateListing(Seller $seller, int $listingId, array $data): ShopProduct
    {
        return DB::transaction(function () use ($seller, $listingId, $data) {
            /** @var ShopProduct|null $listing */
            $listing = ShopProduct::where('seller_id', $seller->id)
                ->where('id', $listingId)
                ->lockForUpdate()
                ->first();

            if (!$listing) {
                throw new CatalogException("Listing #{$listingId} not found.", 'LISTING_NOT_FOUND', 404);
            }

            /** @var GlobalProduct|null $globalProduct */
            $globalProduct = GlobalProduct::withTrashed()->find($listing->global_product_id);
            $isUnavailable = !$globalProduct || !$globalProduct->is_active || $globalProduct->trashed();

            // When master product is inactive or soft-deleted, only deactivation (is_active = false) is allowed
            if ($isUnavailable) {
                $tryingToActivate = isset($data['is_active']) && $data['is_active'];
                $hasPriceEdit = array_key_exists('custom_price', $data);
                $hasStockEdit = isset($data['stock_quantity']) || isset($data['stock_adjust']);

                if ($tryingToActivate || $hasPriceEdit || $hasStockEdit) {
                    throw new CatalogException(
                        'This master catalog product is unavailable or inactive. Only deactivation is permitted.',
                        'PRODUCT_UNAVAILABLE',
                        422
                    );
                }
            }

            // Stock update
            if (isset($data['stock_quantity'])) {
                $listing->stock_quantity = (int) $data['stock_quantity'];
            } elseif (isset($data['stock_adjust'])) {
                $newStock = (int) $listing->stock_quantity + (int) $data['stock_adjust'];
                if ($newStock < 0) {
                    throw new CatalogException(
                        'Stock adjustment would reduce inventory below zero.',
                        'STOCK_BELOW_ZERO',
                        422
                    );
                }
                $listing->stock_quantity = $newStock;
            }

            // Price update
            if (array_key_exists('custom_price', $data)) {
                if ($data['custom_price'] === null) {
                    $listing->custom_price = null; // Reset to base price
                } else {
                    $price = (float) $data['custom_price'];
                    $basePrice = (float) ($globalProduct?->base_price ?? 0);
                    $multiplier = (float) config('bakala_orders.catalog_price_max_multiplier', 3.0);
                    $maxAllowed = round($basePrice * $multiplier, 2);

                    if ($price > $maxAllowed) {
                        throw new CatalogException(
                            "Custom price cannot exceed {$multiplier}x of base price (max allowed: PKR {$maxAllowed}).",
                            'PRICE_EXCEEDS_MAX_MULTIPLIER',
                            422
                        );
                    }

                    $listing->custom_price = $price;
                }
            }

            // Status update
            if (isset($data['is_active'])) {
                $listing->is_active = (bool) $data['is_active'];
            }

            $listing->save();

            return $listing->fresh(['globalProduct.category']);
        });
    }

    /**
     * Bulk price adjustments (increase_percent, decrease_percent, reset_to_base).
     */
    public function bulkPrice(Seller $seller, array $data): array
    {
        $mode = $data['mode'];
        $percent = isset($data['percent']) ? (float) $data['percent'] : null;
        $preview = !empty($data['preview']);
        $multiplier = (float) config('bakala_orders.catalog_price_max_multiplier', 3.0);

        // Find candidate listings belonging to this seller
        $query = ShopProduct::with('globalProduct')
            ->where('seller_id', $seller->id);

        $listingIds = $data['listing_ids'] ?? null;
        if (!empty($listingIds)) {
            $query->whereIn('id', array_map('intval', (array) $listingIds));
        } elseif (($data['scope'] ?? '') === 'category' && !empty($data['category_id'])) {
            $catId = (int) $data['category_id'];
            $query->whereHas('globalProduct', function ($q) use ($catId) {
                $q->where('catalog_category_id', $catId);
            });
        }

        $listings = $query->get();

        // Preview mode: calculate without saving
        if ($preview) {
            $previewItems = [];
            $limit = min(100, $listings->count());

            for ($i = 0; $i < $limit; $i++) {
                $listing = $listings[$i];
                $global = $listing->globalProduct;

                if (!$global || !$global->is_active || $global->trashed()) {
                    $previewItems[] = [
                        'listing_id' => (int) $listing->id,
                        'name' => (string) ($global?->name ?? 'Unknown'),
                        'old_price' => number_format((float) ($listing->custom_price ?? $global?->base_price ?? 0), 2, '.', ''),
                        'new_price' => null,
                        'status' => 'skipped',
                        'reason' => 'product_unavailable',
                    ];
                    continue;
                }

                $basePrice = (float) $global->base_price;
                $oldPrice = (float) ($listing->custom_price ?? $basePrice);
                $newPrice = null;
                $status = 'valid';
                $reason = null;

                if ($mode === 'reset_to_base') {
                    $newPrice = $basePrice;
                } elseif ($mode === 'increase_percent') {
                    $calc = round($oldPrice * (1 + $percent / 100), 2);
                    $maxAllowed = round($basePrice * $multiplier, 2);
                    if ($calc > $maxAllowed) {
                        $status = 'skipped';
                        $reason = 'price_exceeds_max_multiplier';
                    } else {
                        $newPrice = $calc;
                    }
                } elseif ($mode === 'decrease_percent') {
                    $calc = round($oldPrice * (1 - $percent / 100), 2);
                    if ($calc <= 0) {
                        $status = 'skipped';
                        $reason = 'price_below_zero';
                    } else {
                        $newPrice = $calc;
                    }
                }

                $previewItems[] = [
                    'listing_id' => (int) $listing->id,
                    'name' => (string) $global->name,
                    'old_price' => number_format($oldPrice, 2, '.', ''),
                    'new_price' => $newPrice !== null ? number_format($newPrice, 2, '.', '') : null,
                    'status' => $status,
                    'reason' => $reason,
                ];
            }

            return [
                'preview' => true,
                'count' => count($previewItems),
                'total_considered' => $listings->count(),
                'items' => $previewItems,
            ];
        }

        // Apply bulk changes in a single transaction
        return DB::transaction(function () use ($listings, $mode, $percent, $multiplier) {
            $updatedCount = 0;
            $skippedCount = 0;
            $skippedDetails = [];

            foreach ($listings as $listing) {
                /** @var ShopProduct $lockedListing */
                $lockedListing = ShopProduct::whereKey($listing->id)->lockForUpdate()->first();
                $global = GlobalProduct::withTrashed()->find($lockedListing->global_product_id);

                if (!$global || !$global->is_active || $global->trashed()) {
                    $skippedCount++;
                    $skippedDetails[] = [
                        'listing_id' => (int) $lockedListing->id,
                        'name' => (string) ($global?->name ?? 'Unknown'),
                        'reason' => 'product_unavailable',
                    ];
                    continue;
                }

                $basePrice = (float) $global->base_price;
                $currentEffective = (float) ($lockedListing->custom_price ?? $basePrice);

                if ($mode === 'reset_to_base') {
                    $lockedListing->custom_price = null;
                    $lockedListing->save();
                    $updatedCount++;
                } elseif ($mode === 'increase_percent') {
                    $calc = round($currentEffective * (1 + $percent / 100), 2);
                    $maxAllowed = round($basePrice * $multiplier, 2);

                    if ($calc > $maxAllowed) {
                        $skippedCount++;
                        $skippedDetails[] = [
                            'listing_id' => (int) $lockedListing->id,
                            'name' => (string) $global->name,
                            'reason' => 'price_exceeds_max_multiplier',
                            'calculated_price' => $calc,
                            'max_allowed' => $maxAllowed,
                        ];
                        continue;
                    }

                    $lockedListing->custom_price = $calc;
                    $lockedListing->save();
                    $updatedCount++;
                } elseif ($mode === 'decrease_percent') {
                    $calc = round($currentEffective * (1 - $percent / 100), 2);

                    if ($calc <= 0) {
                        $skippedCount++;
                        $skippedDetails[] = [
                            'listing_id' => (int) $lockedListing->id,
                            'name' => (string) $global->name,
                            'reason' => 'price_below_zero',
                            'calculated_price' => $calc,
                        ];
                        continue;
                    }

                    $lockedListing->custom_price = $calc;
                    $lockedListing->save();
                    $updatedCount++;
                }
            }

            return [
                'preview' => false,
                'updated' => $updatedCount,
                'updated_count' => $updatedCount,
                'skipped' => $skippedCount,
                'skipped_count' => $skippedCount,
                'skipped_details' => $skippedDetails,
            ];
        });
    }
}
