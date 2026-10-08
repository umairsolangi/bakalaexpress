<?php

namespace App\Services\Api;

use App\Http\Resources\Api\RiderAvailableOrderResource;
use App\Http\Resources\Api\RiderHistoryOrderResource;
use App\Http\Resources\Api\RiderOrderDetailResource;
use App\Http\Resources\Api\RiderOrderShortResource;
use App\Mail\OrderDeliveredMail;
use App\Models\Order;
use App\Models\Rider;
use App\Notifications\OrderStatusNotification;
use App\Support\Api\SanitizedDeliveryProofUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class RiderOrderService
{
    /**
     * 1. GET /dashboard
     */
    public function getDashboard(Rider $rider): JsonResponse
    {
        $activeOrders = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned_to_rider', 'picked_up'])
            ->with(['seller', 'user', 'items'])
            ->latest('updated_at')
            ->get();

        $todayDeliveredCount = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $todayEarnings = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereDate('updated_at', now()->toDateString())
            ->sum('delivery_charges');

        $data = [
            'rider' => [
                'id' => (int) $rider->id,
                'name' => (string) $rider->name,
                'status' => (string) $rider->status,
                'vehicle_type' => (string) ($rider->vehicle_type ?? 'Motorcycle'),
            ],
            'today_delivered_count' => (int) $todayDeliveredCount,
            'today_earnings' => number_format((float) $todayEarnings, 2, '.', ''),
            'earnings_note' => 'Rider earnings are calculated from delivery charges of delivered orders. Delivery charges are currently 0.00 in the platform.',
            'active_orders' => RiderOrderShortResource::collection($activeOrders),
        ];

        $meta = [
            'server_time' => now()->toIso8601String(),
        ];

        return ApiResponse::success($data, 'Dashboard data retrieved successfully.', $meta);
    }

    /**
     * 2. POST /status
     */
    public function updateStatus(Rider $rider, string $targetStatus): JsonResponse
    {
        if ($targetStatus === 'offline') {
            $hasActiveOrders = Order::where('rider_id', $rider->id)
                ->whereIn('status', ['assigned_to_rider', 'picked_up'])
                ->exists();

            if ($hasActiveOrders) {
                return ApiResponse::error(
                    'Cannot go offline while you have active orders in progress.',
                    'HAS_ACTIVE_ORDERS',
                    [],
                    422
                );
            }
        }

        $rider->status = $targetStatus;
        $rider->save();

        return ApiResponse::success([
            'id' => (int) $rider->id,
            'name' => (string) $rider->name,
            'status' => (string) $rider->status,
        ], "Rider status updated to {$targetStatus}.");
    }

    /**
     * 3. GET /orders/available
     */
    public function getAvailableOrders(Rider $rider, int $perPage): JsonResponse
    {
        if ($rider->status !== 'online') {
            return ApiResponse::error(
                'You must be online to view available delivery requests.',
                'RIDER_OFFLINE',
                [],
                422
            );
        }

        $paginator = Order::where('status', 'ready_for_pickup')
            ->whereNull('rider_id')
            ->with(['seller', 'items'])
            ->orderBy('updated_at', 'asc') // Oldest first
            ->paginate($perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'server_time' => now()->toIso8601String(),
            'suggested_poll_seconds' => 15,
        ];

        return ApiResponse::success(
            RiderAvailableOrderResource::collection($paginator->items()),
            'Available orders retrieved successfully.',
            $meta
        );
    }

    /**
     * 4. GET /orders/current
     */
    public function getCurrentOrders(Rider $rider): JsonResponse
    {
        $orders = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned_to_rider', 'picked_up'])
            ->with(['seller', 'user', 'items.product', 'items.globalProduct'])
            ->latest('updated_at')
            ->get();

        return ApiResponse::success(
            RiderOrderDetailResource::collection($orders),
            'Current active orders retrieved successfully.'
        );
    }

    /**
     * 5. GET /orders/{order}
     */
    public function getOrderDetail(Rider $rider, int $orderId): JsonResponse
    {
        $order = Order::with(['seller', 'user', 'items.product', 'items.globalProduct'])
            ->whereKey($orderId)
            ->first();

        if (! $order || (int) $order->rider_id !== (int) $rider->id) {
            return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
        }

        return ApiResponse::success(
            new RiderOrderDetailResource($order),
            'Order detail retrieved successfully.'
        );
    }

    /**
     * 6. POST /orders/{order}/accept
     */
    public function acceptOrder(Rider $rider, int $orderId): JsonResponse
    {
        if ($rider->status !== 'online') {
            return ApiResponse::error(
                'You must be online to accept delivery requests.',
                'RIDER_OFFLINE',
                [],
                422
            );
        }

        $maxActive = (int) config('bakala_orders.rider_max_active_orders', 2);
        $activeCount = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned_to_rider', 'picked_up'])
            ->count();

        if ($activeCount >= $maxActive) {
            return ApiResponse::error(
                "You cannot accept more than {$maxActive} active orders concurrently.",
                'TOO_MANY_ACTIVE_ORDERS',
                [],
                422
            );
        }

        return DB::transaction(function () use ($rider, $orderId) {
            $existing = Order::whereKey($orderId)->lockForUpdate()->first();

            if (! $existing) {
                return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
            }

            if ($existing->rider_id !== null) {
                return ApiResponse::error(
                    'This order has already been accepted by another rider.',
                    'ORDER_ALREADY_TAKEN',
                    [],
                    409
                );
            }

            if ($existing->status !== 'ready_for_pickup') {
                return ApiResponse::error(
                    'Order is no longer available for pickup.',
                    'ORDER_NOT_AVAILABLE',
                    [],
                    422
                );
            }

            // Atomic claim via query builder update (Order::$fillable unchanged)
            $updated = Order::where('id', $orderId)
                ->where('status', 'ready_for_pickup')
                ->whereNull('rider_id')
                ->update([
                    'rider_id' => $rider->id,
                    'status' => 'assigned_to_rider',
                ]);

            if ($updated === 0) {
                $refreshed = Order::whereKey($orderId)->first();
                if ($refreshed && $refreshed->rider_id !== null) {
                    return ApiResponse::error(
                        'This order has already been accepted by another rider.',
                        'ORDER_ALREADY_TAKEN',
                        [],
                        409
                    );
                }

                return ApiResponse::error(
                    'Order is no longer available for pickup.',
                    'ORDER_NOT_AVAILABLE',
                    [],
                    422
                );
            }

            $order = Order::with(['seller', 'user', 'items.product', 'items.globalProduct'])
                ->whereKey($orderId)
                ->first();

            // Customer notification (isolated in try/catch to never fail acceptance)
            try {
                if ($order && $order->user) {
                    $order->user->notify(new OrderStatusNotification($order, 'assigned_to_rider', $rider->name));
                }
            } catch (\Throwable $e) {
                Log::warning('OrderStatusNotification failed on order accept', [
                    'order_id' => $orderId,
                    'rider_id' => $rider->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return ApiResponse::success(
                new RiderOrderDetailResource($order),
                'Order accepted successfully.'
            );
        });
    }

    /**
     * 7. POST /orders/{order}/pickup
     */
    public function pickupOrder(Rider $rider, int $orderId): JsonResponse
    {
        $order = Order::whereKey($orderId)->first();

        if (! $order || (int) $order->rider_id !== (int) $rider->id) {
            return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
        }

        return DB::transaction(function () use ($rider, $orderId) {
            /** @var Order|null $lockedOrder */
            $lockedOrder = Order::with(['seller', 'user', 'items.product', 'items.globalProduct'])
                ->whereKey($orderId)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || (int) $lockedOrder->rider_id !== (int) $rider->id) {
                return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
            }

            if ($lockedOrder->status !== 'assigned_to_rider') {
                return ApiResponse::error(
                    "Order cannot be picked up from current status '{$lockedOrder->status}'. Expected status 'assigned_to_rider'.",
                    'INVALID_STATUS_TRANSITION',
                    [],
                    422
                );
            }

            $lockedOrder->status = 'picked_up';
            $lockedOrder->save();

            // Notify customer of pickup
            try {
                if ($lockedOrder->user) {
                    $lockedOrder->user->notify(new OrderStatusNotification($lockedOrder, 'picked_up', $rider->name));
                }
            } catch (\Throwable $e) {
                Log::warning('OrderStatusNotification failed on order pickup', [
                    'order_id' => $orderId,
                    'rider_id' => $rider->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return ApiResponse::success(
                new RiderOrderDetailResource($lockedOrder),
                'Order marked as picked up.'
            );
        });
    }

    /**
     * 8. POST /orders/{order}/deliver
     */
    public function deliverOrder(Rider $rider, int $orderId, ?UploadedFile $proofFile): JsonResponse
    {
        $order = Order::whereKey($orderId)->first();

        if (! $order || (int) $order->rider_id !== (int) $rider->id) {
            return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
        }

        if ($order->status !== 'picked_up') {
            return ApiResponse::error(
                "Order cannot be delivered from current status '{$order->status}'. Expected status 'picked_up'.",
                'INVALID_STATUS_TRANSITION',
                [],
                422
            );
        }

        $proofPath = null;
        if ($proofFile !== null) {
            try {
                $proofPath = SanitizedDeliveryProofUpload::store($proofFile);
            } catch (RuntimeException $e) {
                return ApiResponse::error(
                    $e->getMessage(),
                    'PROOF_IMAGE_PROCESSING_FAILED',
                    ['proof_image' => [$e->getMessage()]],
                    422
                );
            }
        }

        return DB::transaction(function () use ($rider, $orderId, $proofPath) {
            /** @var Order|null $lockedOrder */
            $lockedOrder = Order::with(['seller', 'user', 'items.product', 'items.globalProduct'])
                ->whereKey($orderId)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || (int) $lockedOrder->rider_id !== (int) $rider->id) {
                return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
            }

            if ($lockedOrder->status !== 'picked_up') {
                return ApiResponse::error(
                    "Order cannot be delivered from current status '{$lockedOrder->status}'. Expected status 'picked_up'.",
                    'INVALID_STATUS_TRANSITION',
                    [],
                    422
                );
            }

            if ($proofPath) {
                $lockedOrder->delivery_proof_image = $proofPath;
            }

            $lockedOrder->status = 'delivered';
            if (! $lockedOrder->estimated_delivery_at) {
                $lockedOrder->estimated_delivery_at = now();
            }
            $lockedOrder->save();

            // Reset rider status back to online if they were busy
            $rider->status = 'online';
            $rider->save();

            // Customer notification and email (try/catch ensures failures never rollback delivery)
            try {
                $user = $lockedOrder->user;
                if ($user) {
                    $user->notify(new OrderStatusNotification($lockedOrder, 'delivered', $rider->name));

                    if (! empty($user->email)) {
                        Mail::to($user->email)->send(new OrderDeliveredMail($lockedOrder));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Customer notification/email failed on order delivery', [
                    'order_id' => $orderId,
                    'rider_id' => $rider->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return ApiResponse::success(
                new RiderOrderDetailResource($lockedOrder),
                'Order delivered successfully.'
            );
        });
    }

    /**
     * 9. GET /history
     */
    public function getHistory(Rider $rider, ?string $from, ?string $to, int $perPage): JsonResponse
    {
        $query = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->with(['seller', 'user', 'items']);

        if ($from) {
            $query->whereDate('updated_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('updated_at', '<=', $to);
        }

        $paginator = $query->orderBy('updated_at', 'desc')->paginate($perPage);

        // Lifetime and today summary calculations
        $totalDeliveries = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->count();

        $totalEarnings = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->sum('delivery_charges');

        $todayDeliveries = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $todayEarnings = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereDate('updated_at', now()->toDateString())
            ->sum('delivery_charges');

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'total_deliveries' => (int) $totalDeliveries,
            'total_earnings' => number_format((float) $totalEarnings, 2, '.', ''),
            'today_deliveries' => (int) $todayDeliveries,
            'today_earnings' => number_format((float) $todayEarnings, 2, '.', ''),
            'earnings_note' => 'Rider earnings are calculated from delivery charges of delivered orders. Delivery charges are currently 0.00 in the platform.',
        ];

        return ApiResponse::success(
            RiderHistoryOrderResource::collection($paginator->items()),
            'Fulfillment history retrieved successfully.',
            $meta
        );
    }
}
