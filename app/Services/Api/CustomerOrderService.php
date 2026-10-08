<?php

namespace App\Services\Api;

use App\Http\Resources\Api\OrderDetailResource;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderPlacedMail;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerOrderService
{
    /**
     * Validate cart items without modifying stock.
     */
    public function validateCartItems(array $itemsInput): array|JsonResponse
    {
        $listingIds = array_column($itemsInput, 'listing_id');
        $qtyMap = [];
        foreach ($itemsInput as $it) {
            $qtyMap[(int) $it['listing_id']] = (int) $it['quantity'];
        }

        $listings = ShopProduct::with(['globalProduct', 'seller'])
            ->whereIn('id', $listingIds)
            ->get()
            ->keyBy('id');

        if ($listings->isEmpty()) {
            return ApiResponse::error(
                'None of the selected products were found in the catalog.',
                'ITEMS_NOT_FOUND',
                [],
                422
            );
        }

        // Single Seller Rule: ensure all items belong to exactly one seller
        $sellerIds = $listings->pluck('seller_id')->unique()->values();
        if ($sellerIds->count() > 1) {
            return ApiResponse::error(
                'All items in your cart must belong to a single seller.',
                'MULTIPLE_SELLERS',
                [],
                422
            );
        }

        $sellerId = $sellerIds->first();
        /** @var Seller|null $seller */
        $seller = Seller::where('id', $sellerId)
            ->where('accountIsApproved', 1)
            ->where('is_deleted', false)
            ->first();

        if (!$seller || !$seller->isAcceptingOrders()) {
            return ApiResponse::error(
                'This seller is currently closed or not accepting orders.',
                'SELLER_CLOSED',
                [],
                422
            );
        }

        $evaluatedItems = [];
        $subtotalPaisa = 0;
        $allOk = true;

        foreach ($itemsInput as $itemInput) {
            $listingId = (int) $itemInput['listing_id'];
            $reqQty = (int) $itemInput['quantity'];

            /** @var ShopProduct|null $listing */
            $listing = $listings->get($listingId);

            if (!$listing) {
                $allOk = false;
                $evaluatedItems[] = [
                    'listing_id' => $listingId,
                    'name' => 'Unavailable Item',
                    'unit_type' => null,
                    'image' => null,
                    'unit_price' => '0.00',
                    'quantity' => $reqQty,
                    'line_total' => '0.00',
                    'available_stock' => 0,
                    'ok' => false,
                    'problem_code' => 'INACTIVE',
                ];
                continue;
            }

            $global = $listing->globalProduct;
            $isGlobalActive = $global && $global->is_active && !$global->trashed();
            $isListingActive = $listing->is_active && $isGlobalActive;

            $stock = (int) ($listing->stock_quantity ?? 0);

            $ok = true;
            $problemCode = null;

            if (!$isListingActive) {
                $ok = false;
                $problemCode = 'INACTIVE';
            } elseif ($stock <= 0) {
                $ok = false;
                $problemCode = 'OUT_OF_STOCK';
            } elseif ($stock < $reqQty) {
                $ok = false;
                $problemCode = 'LIMITED_STOCK';
            }

            if (!$ok) {
                $allOk = false;
            }

            // Database money-safe math in integer paisa
            $effectivePrice = $listing->custom_price ?? $global?->base_price ?? 0;
            $unitPricePaisa = (int) bcmul((string) $effectivePrice, '100', 0);
            $lineTotalPaisa = $unitPricePaisa * $reqQty;
            $subtotalPaisa += $lineTotalPaisa;

            $imageUrl = $global?->display_image_url;
            if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
                $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
            }

            $evaluatedItems[] = [
                'listing_id' => $listing->id,
                'name' => (string) ($global?->name ?? 'Product'),
                'unit_type' => $global?->unit_type ? (string) $global->unit_type : null,
                'image' => $imageUrl,
                'unit_price' => number_format($unitPricePaisa / 100, 2, '.', ''),
                'quantity' => $reqQty,
                'line_total' => number_format($lineTotalPaisa / 100, 2, '.', ''),
                'available_stock' => $stock,
                'ok' => $ok,
                'problem_code' => $problemCode,
            ];
        }

        $deliveryFeePaisa = (int) bcmul((string) config('bakala_orders.delivery_fee', 0), '100', 0);
        $totalPaisa = max(0, $subtotalPaisa + $deliveryFeePaisa);

        return [
            'is_valid' => $allOk,
            'seller' => $seller,
            'items' => $evaluatedItems,
            'subtotal_paisa' => $subtotalPaisa,
            'subtotal_float' => $subtotalPaisa / 100,
            'totals' => [
                'subtotal' => number_format($subtotalPaisa / 100, 2, '.', ''),
                'delivery_charges' => number_format($deliveryFeePaisa / 100, 2, '.', ''),
                'discount' => '0.00',
                'total' => number_format($totalPaisa / 100, 2, '.', ''),
            ],
        ];
    }

    /**
     * Verify and calculate promo code discount on top of cart validation.
     */
    public function applyPromo(array $itemsInput, string $promoCodeInput): array|JsonResponse
    {
        $cartResult = $this->validateCartItems($itemsInput);
        if ($cartResult instanceof JsonResponse) {
            return $cartResult;
        }

        $code = strtoupper(trim($promoCodeInput));
        /** @var PromoCode|null $promo */
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo || !$promo->is_active) {
            return ApiResponse::error(
                'Promo code is invalid.',
                'PROMO_INVALID',
                [],
                422
            );
        }

        if ($promo->starts_at && $promo->starts_at->isFuture()) {
            return ApiResponse::error(
                'Promo code is not active yet.',
                'PROMO_INVALID',
                [],
                422
            );
        }

        if ($promo->expires_at && $promo->expires_at->isPast()) {
            return ApiResponse::error(
                'Promo code has expired.',
                'PROMO_EXPIRED',
                [],
                422
            );
        }

        if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
            return ApiResponse::error(
                'Promo code redemption limit has been reached.',
                'PROMO_LIMIT_REACHED',
                [],
                422
            );
        }

        $subtotalPaisa = $cartResult['subtotal_paisa'];
        $minOrderPaisa = (int) bcmul((string) ($promo->minimum_order_amount ?? 0), '100', 0);

        if ($subtotalPaisa < $minOrderPaisa) {
            return ApiResponse::error(
                'Minimum order amount of ' . number_format($minOrderPaisa / 100, 2) . ' PKR required for this promo code.',
                'PROMO_MIN_ORDER_NOT_MET',
                [],
                422
            );
        }

        // Calculate discount in integer paisa
        if ($promo->discount_type === 'percent') {
            $discountPaisa = (int) round(($subtotalPaisa * (float) $promo->discount_value) / 100);
        } else {
            $discountPaisa = (int) bcmul((string) $promo->discount_value, '100', 0);
        }

        if ($promo->maximum_discount_amount !== null) {
            $maxDiscountPaisa = (int) bcmul((string) $promo->maximum_discount_amount, '100', 0);
            $discountPaisa = min($discountPaisa, $maxDiscountPaisa);
        }

        $discountPaisa = min($discountPaisa, $subtotalPaisa);
        $deliveryFeePaisa = (int) bcmul((string) config('bakala_orders.delivery_fee', 0), '100', 0);
        $totalPaisa = max(0, $subtotalPaisa + $deliveryFeePaisa - $discountPaisa);

        return [
            'is_valid' => $cartResult['is_valid'],
            'items' => $cartResult['items'],
            'promo' => [
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
            ],
            'totals' => [
                'subtotal' => number_format($subtotalPaisa / 100, 2, '.', ''),
                'delivery_charges' => number_format($deliveryFeePaisa / 100, 2, '.', ''),
                'discount' => number_format($discountPaisa / 100, 2, '.', ''),
                'total' => number_format($totalPaisa / 100, 2, '.', ''),
            ],
        ];
    }

    /**
     * Place a customer order with transactional row locks and idempotency caching.
     */
    public function placeOrder(User $customer, array $data, string $idempotencyKey): JsonResponse
    {
        // Abuse protection: limit active orders
        $activeStatuses = config('bakala_orders.active_statuses', [
            'pending',
            'confirmed_by_seller',
            'preparing',
            'ready_for_pickup',
            'assigned_to_rider',
            'picked_up',
        ]);

        $activeOrdersCount = Order::where('user_id', $customer->id)
            ->whereIn('status', $activeStatuses)
            ->count();

        $maxActive = (int) config('bakala_orders.max_active_orders', 3);
        if ($activeOrdersCount >= $maxActive) {
            return ApiResponse::error(
                "You cannot place new orders while you have {$maxActive} active orders. Please wait for an existing order to complete.",
                'TOO_MANY_ACTIVE_ORDERS',
                [],
                422
            );
        }

        // Idempotency: verify if this key was already successfully fulfilled
        $cacheKey = "idempotency:order:user_{$customer->id}:{$idempotencyKey}";
        $cachedOrderId = Cache::get($cacheKey);
        if ($cachedOrderId) {
            $existingOrder = Order::with(['seller', 'items', 'rider'])
                ->where('user_id', $customer->id)
                ->find($cachedOrderId);

            if ($existingOrder) {
                return ApiResponse::success(
                    new OrderDetailResource($existingOrder),
                    'Order retrieved via idempotent replay.',
                    ['idempotent_replay' => true],
                    200
                );
            }
        }

        // Concurrency lock per customer prevents simultaneous double order creation
        $lock = Cache::lock("order_create_lock:user_{$customer->id}", 15);

        return $lock->block(10, function () use ($customer, $data, $idempotencyKey, $cacheKey) {
            // Re-check cache inside lock (double-checked locking)
            $cachedOrderId = Cache::get($cacheKey);
            if ($cachedOrderId) {
                $existingOrder = Order::with(['seller', 'items', 'rider'])
                    ->where('user_id', $customer->id)
                    ->find($cachedOrderId);

                if ($existingOrder) {
                    return ApiResponse::success(
                        new OrderDetailResource($existingOrder),
                        'Order retrieved via idempotent replay.',
                        ['idempotent_replay' => true],
                        200
                    );
                }
            }

            try {
                $order = DB::transaction(function () use ($customer, $data) {
                    $itemsInput = $data['items'];
                    $listingIds = array_column($itemsInput, 'listing_id');

                    // Lock listing rows to prevent race conditions
                    $listings = ShopProduct::with(['globalProduct', 'seller'])
                        ->whereIn('id', $listingIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    if ($listings->count() !== count(array_unique($listingIds))) {
                        throw new \DomainException('One or more selected products no longer exist in the catalog.');
                    }

                    $sellerIds = $listings->pluck('seller_id')->unique()->values();
                    if ($sellerIds->count() !== 1) {
                        throw new \DomainException('All order items must belong to a single seller.');
                    }

                    $seller = Seller::where('id', $sellerIds->first())
                        ->where('accountIsApproved', 1)
                        ->where('is_deleted', false)
                        ->first();

                    if (!$seller || !$seller->isAcceptingOrders()) {
                        throw new \DomainException('This seller is currently closed.');
                    }

                    $subtotalPaisa = 0;
                    $itemSnapshots = [];

                    foreach ($itemsInput as $it) {
                        $listingId = (int) $it['listing_id'];
                        $reqQty = (int) $it['quantity'];

                        /** @var ShopProduct $listing */
                        $listing = $listings->get($listingId);
                        $global = $listing->globalProduct;

                        if (!$listing->is_active || !$global || !$global->is_active || $global->trashed()) {
                            throw new \DomainException("Item '{$global?->name}' is no longer available.");
                        }

                        if ($listing->stock_quantity < $reqQty) {
                            throw new \DomainException("Insufficient stock available for '{$global?->name}'.");
                        }

                        $effectivePrice = $listing->custom_price ?? $global->base_price ?? 0;
                        $unitPricePaisa = (int) bcmul((string) $effectivePrice, '100', 0);
                        $lineTotalPaisa = $unitPricePaisa * $reqQty;
                        $subtotalPaisa += $lineTotalPaisa;

                        $itemSnapshots[] = [
                            'shop_product_id' => $listing->id,
                            'global_product_id' => $listing->global_product_id,
                            'item_name' => (string) $global->name,
                            'unit_type' => $global->unit_type ? (string) $global->unit_type : null,
                            'item_image' => $global->display_image_url,
                            'quantity' => $reqQty,
                            'price' => number_format($unitPricePaisa / 100, 2, '.', ''),
                        ];
                    }

                    // Promo code handling inside same transaction
                    $promoCode = null;
                    $discountPaisa = 0;

                    if (!empty($data['promo_code'])) {
                        $code = strtoupper(trim((string) $data['promo_code']));
                        $promoCode = PromoCode::where('code', $code)->lockForUpdate()->first();

                        if (!$promoCode || !$promoCode->is_active) {
                            throw new \DomainException('Promo code is invalid.');
                        }

                        if ($promoCode->starts_at && $promoCode->starts_at->isFuture()) {
                            throw new \DomainException('Promo code has not started yet.');
                        }

                        if ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
                            throw new \DomainException('Promo code has expired.');
                        }

                        if ($promoCode->usage_limit !== null && $promoCode->used_count >= $promoCode->usage_limit) {
                            throw new \DomainException('Promo code usage limit has been reached.');
                        }

                        $minOrderPaisa = (int) bcmul((string) ($promoCode->minimum_order_amount ?? 0), '100', 0);
                        if ($subtotalPaisa < $minOrderPaisa) {
                            throw new \DomainException('Minimum order amount not met for promo code.');
                        }

                        if ($promoCode->discount_type === 'percent') {
                            $discountPaisa = (int) round(($subtotalPaisa * (float) $promoCode->discount_value) / 100);
                        } else {
                            $discountPaisa = (int) bcmul((string) $promoCode->discount_value, '100', 0);
                        }

                        if ($promoCode->maximum_discount_amount !== null) {
                            $maxDiscountPaisa = (int) bcmul((string) $promoCode->maximum_discount_amount, '100', 0);
                            $discountPaisa = min($discountPaisa, $maxDiscountPaisa);
                        }

                        $discountPaisa = min($discountPaisa, $subtotalPaisa);

                        // Increment promo used_count at placement (mirroring web behavior)
                        if ($discountPaisa > 0) {
                            $promoCode->increment('used_count');
                        }
                    }

                    $deliveryFeePaisa = (int) bcmul((string) config('bakala_orders.delivery_fee', 0), '100', 0);
                    $totalPaisa = max(0, $subtotalPaisa + $deliveryFeePaisa - $discountPaisa);

                    $deliverySector = $data['delivery_sector'] ?? $customer->sector ?? null;
                    $deliveryNearArea = $data['delivery_near_area'] ?? $customer->near_area ?? null;

                    // Create order without modifying Order.php $fillable
                    $order = new Order();
                    $order->forceFill([
                        'user_id' => $customer->id,
                        'seller_id' => $seller->id,
                        'address' => $data['address'],
                        'phone' => $data['phone'],
                        'status' => 'pending',
                        'total_amount' => number_format($totalPaisa / 100, 2, '.', ''),
                        'delivery_charges' => number_format($deliveryFeePaisa / 100, 2, '.', ''),
                        'transaction_id' => null, // COD only
                        'promo_code_id' => $promoCode?->id,
                        'discount_amount' => number_format($discountPaisa / 100, 2, '.', ''),
                        'estimated_delivery_at' => now()->addMinutes((int) config('bakala_orders.estimated_delivery_minutes', 45)),
                        'delivery_instructions' => $data['delivery_instructions'] ?? null,
                        'delivery_sector' => $deliverySector,
                        'delivery_near_area' => $deliveryNearArea,
                    ]);
                    $order->save();

                    // Create price snapshots (OrderItem)
                    foreach ($itemSnapshots as $snapshot) {
                        $orderItem = new OrderItem();
                        $orderItem->forceFill([
                            'order_id' => $order->id,
                            'shop_product_id' => $snapshot['shop_product_id'],
                            'global_product_id' => $snapshot['global_product_id'],
                            'product_id' => null,
                            'item_name' => $snapshot['item_name'],
                            'unit_type' => $snapshot['unit_type'],
                            'item_image' => $snapshot['item_image'],
                            'quantity' => $snapshot['quantity'],
                            'price' => $snapshot['price'],
                        ]);
                        $orderItem->save();
                    }

                    return $order;
                });
            } catch (\DomainException $e) {
                return ApiResponse::error($e->getMessage(), 'ORDER_VALIDATION_FAILED', [], 422);
            }

            // Cache idempotency mapping for 24 hours
            $ttlHours = (int) config('bakala_orders.idempotency_ttl_hours', 24);
            Cache::put($cacheKey, $order->id, now()->addHours($ttlHours));

            // Side effects after commit (wrapped in try/catch to protect order placement)
            try {
                $seller = Seller::find($order->seller_id);
                if ($seller) {
                    $orderItemsFormatted = $order->items->map(function ($it) {
                        return [
                            'service_id' => $it->shop_product_id ?? $it->global_product_id ?? 1,
                            'quantity' => $it->quantity,
                            'price' => $it->price,
                        ];
                    })->all();
                    $seller->notify(new NewOrderNotification(
                        $seller->name,
                        $order->id,
                        json_encode($orderItemsFormatted)
                    ));
                }
            } catch (\Throwable $e) {
                Log::warning('NewOrderNotification dispatch failed: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                ]);
            }

            try {
                $order->load(['items', 'seller', 'user']);
                if ($customer->email) {
                    Mail::to($customer->email)->send(new OrderPlacedMail($order));
                }
            } catch (\Throwable $e) {
                Log::warning('OrderPlacedMail dispatch failed: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                ]);
            }

            return ApiResponse::success(
                new OrderDetailResource($order->fresh(['seller', 'items', 'rider'])),
                'Order placed successfully.',
                [],
                201
            );
        });
    }

    /**
     * Cancel an active order with inventory restoration if previously reserved.
     */
    public function cancelOrder(User $customer, int $orderId, ?string $reason): JsonResponse
    {
        return DB::transaction(function () use ($customer, $orderId, $reason) {
            /** @var Order|null $lockedOrder */
            $lockedOrder = Order::with('items')
                ->where('user_id', $customer->id)
                ->whereKey($orderId)
                ->lockForUpdate()
                ->first();

            if (!$lockedOrder) {
                return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
            }

            if (!in_array($lockedOrder->status, ['pending', 'confirmed_by_seller'], true)) {
                return ApiResponse::error(
                    'This order cannot be cancelled in its current status.',
                    'ORDER_NOT_CANCELLABLE',
                    [],
                    422
                );
            }

            // Restore catalog inventory if reserved by seller confirmation
            if ($lockedOrder->inventory_reserved_at !== null) {
                foreach ($lockedOrder->items as $item) {
                    if ($item->shop_product_id) {
                        ShopProduct::whereKey($item->shop_product_id)
                            ->increment('stock_quantity', $item->quantity);
                    }
                }
                $lockedOrder->inventory_reserved_at = null;
            }

            $lockedOrder->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);
            $lockedOrder->save();

            // Dispatch cancellation notifications
            try {
                $customer->notify(new OrderStatusNotification($lockedOrder, 'cancelled'));
            } catch (\Throwable $e) {
                Log::warning('OrderStatusNotification dispatch failed: ' . $e->getMessage());
            }

            // Notify seller of cancellation via push notification (web does not notify seller)
            try {
                if ($lockedOrder->seller) {
                    app(\App\Services\Api\PushService::class)->sendToAccount(
                        $lockedOrder->seller,
                        "Order #{$lockedOrder->id} cancelled",
                        "Customer cancelled order #{$lockedOrder->id}.",
                        [
                            'type' => 'order_status',
                            'order_id' => (string) $lockedOrder->id,
                            'status' => 'cancelled',
                            'role' => 'seller',
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Seller cancellation push failed: ' . $e->getMessage());
            }

            try {
                if ($customer->email) {
                    Mail::to($customer->email)->send(new OrderCancelledMail($lockedOrder));
                }
            } catch (\Throwable $e) {
                Log::warning('OrderCancelledMail dispatch failed: ' . $e->getMessage());
            }

            return ApiResponse::success(
                new OrderDetailResource($lockedOrder->fresh(['seller', 'items', 'rider'])),
                'Order cancelled successfully.'
            );
        });
    }

    /**
     * Submit customer review/feedback for a completed order.
     */
    public function submitFeedback(User $customer, int $orderId, int $rating, string $feedbackText): JsonResponse
    {
        $order = Order::where('user_id', $customer->id)->find($orderId);

        if (!$order) {
            return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
        }

        if (!in_array($order->status, ['delivered', 'completed'], true)) {
            return ApiResponse::error(
                'Feedback can only be submitted for delivered or completed orders.',
                'ORDER_NOT_REVIEWABLE',
                [],
                422
            );
        }

        $alreadyReviewed = Feedback::where('order_id', $order->id)->exists();
        if ($alreadyReviewed) {
            return ApiResponse::error(
                'You have already submitted feedback for this order.',
                'ALREADY_REVIEWED',
                [],
                422
            );
        }

        Feedback::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'seller_id' => $order->seller_id,
            'feedback' => strip_tags($feedbackText),
            'rating' => $rating,
            'status' => 'pending', // Moderation flow matches web
        ]);

        return ApiResponse::success(
            null,
            'Feedback submitted successfully and is pending moderation.',
            [],
            201
        );
    }

    /**
     * Preview reordering items from a past order with current prices and stock availability.
     */
    public function buildReorderSummary(User $customer, int $orderId): JsonResponse
    {
        $order = Order::with('items')->where('user_id', $customer->id)->find($orderId);

        if (!$order) {
            return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
        }

        $items = [];
        $subtotalPaisa = 0;

        foreach ($order->items as $item) {
            $listing = $item->shop_product_id
                ? ShopProduct::with('globalProduct')->find($item->shop_product_id)
                : null;

            if (!$listing) {
                $items[] = [
                    'listing_id' => $item->shop_product_id,
                    'name' => $item->item_name,
                    'current_price' => null,
                    'quantity' => (int) $item->quantity,
                    'available_stock' => 0,
                    'ok' => false,
                    'problem_code' => 'INACTIVE',
                ];
                continue;
            }

            $global = $listing->globalProduct;
            $isGlobalActive = $global && $global->is_active && !$global->trashed();
            $isListingActive = $listing->is_active && $isGlobalActive;
            $stock = (int) ($listing->stock_quantity ?? 0);

            $ok = true;
            $problemCode = null;

            if (!$isListingActive) {
                $ok = false;
                $problemCode = 'INACTIVE';
            } elseif ($stock <= 0) {
                $ok = false;
                $problemCode = 'OUT_OF_STOCK';
            } elseif ($stock < $item->quantity) {
                $ok = false;
                $problemCode = 'LIMITED_STOCK';
            }

            $effectivePrice = $listing->custom_price ?? $global?->base_price ?? 0;
            $currentPriceStr = number_format((float) $effectivePrice, 2, '.', '');

            if ($ok) {
                $unitPaisa = (int) bcmul($currentPriceStr, '100', 0);
                $subtotalPaisa += $unitPaisa * (int) $item->quantity;
            }

            $items[] = [
                'listing_id' => (int) $listing->id,
                'name' => (string) ($global?->name ?? $item->item_name),
                'current_price' => $currentPriceStr,
                'quantity' => (int) $item->quantity,
                'available_stock' => $stock,
                'ok' => $ok,
                'problem_code' => $problemCode,
            ];
        }

        $deliveryFeePaisa = (int) bcmul((string) config('bakala_orders.delivery_fee', 0), '100', 0);
        $totalPaisa = max(0, $subtotalPaisa + ($subtotalPaisa > 0 ? $deliveryFeePaisa : 0));

        return ApiResponse::success([
            'order_id' => $order->id,
            'items' => $items,
            'subtotal' => number_format($subtotalPaisa / 100, 2, '.', ''),
            'delivery_fee' => number_format(($subtotalPaisa > 0 ? $deliveryFeePaisa : 0) / 100, 2, '.', ''),
            'total' => number_format($totalPaisa / 100, 2, '.', ''),
        ], 'Reorder preview retrieved successfully.');
    }
}
