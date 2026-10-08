<?php

namespace App\Services\Api;

use App\Events\OrderReadyForPickup;
use App\Mail\OrderCancelledMail;
use App\Models\Order;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Notifications\OrderStatusNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SellerOrderService
{
    /**
     * Get dashboard summary including order badges, store profile, and today's sales.
     */
    public function getDashboard(Seller $seller): array
    {
        $baseQuery = Order::where('seller_id', $seller->id);

        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $confirmed = (clone $baseQuery)->where('status', 'confirmed_by_seller')->count();
        $preparing = (clone $baseQuery)->where('status', 'preparing')->count();
        $ready = (clone $baseQuery)->where('status', 'ready_for_pickup')->count();
        $delivered = (clone $baseQuery)->where('status', 'delivered')->count();
        $completed = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelled = (clone $baseQuery)->where('status', 'cancelled')->count();
        $rejected = (clone $baseQuery)->where('status', 'rejected')->count();

        $activeTotal = (clone $baseQuery)
            ->whereIn('status', [
                'pending',
                'confirmed_by_seller',
                'preparing',
                'ready_for_pickup',
                'assigned_to_rider',
                'picked_up',
            ])
            ->count();

        $todaySales = (clone $baseQuery)
            ->whereIn('status', ['delivered', 'completed'])
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $todayOrdersCount = (clone $baseQuery)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return [
            'pending' => $pending,
            'confirmed' => $confirmed,
            'preparing' => $preparing,
            'ready' => $ready,
            'active_total' => $activeTotal,
            'delivered' => $delivered,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'rejected' => $rejected,
            'today_sales' => $todaySales,
            'today_orders_count' => $todayOrdersCount,
        ];
    }

    /**
     * List seller orders with grouping, pagination, and sorting.
     */
    public function listOrders(Seller $seller, array $filters = []): array
    {
        $group = $filters['group'] ?? 'all';
        $status = $filters['status'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min(50, $perPage));

        $query = Order::where('seller_id', $seller->id)
            ->with(['user', 'items'])
            ->withCount(['messages as unread_messages' => fn ($q) => $q->where('sender_type', 'user')->where('is_read', false)]);

        if ($status) {
            $query->where('status', $status);
            $query->orderBy('created_at', 'desc');
        } elseif ($group === 'pending') {
            $query->where('status', 'pending');
            $query->orderBy('created_at', 'asc'); // Oldest first for pending orders
        } elseif ($group === 'active') {
            $query->whereIn('status', [
                'confirmed_by_seller',
                'preparing',
                'ready_for_pickup',
                'assigned_to_rider',
                'picked_up',
            ]);
            $query->orderBy('created_at', 'desc');
        } elseif ($group === 'completed') {
            $query->whereIn('status', ['delivered', 'completed']);
            $query->orderBy('created_at', 'desc');
        } elseif ($group === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'rejected']);
            $query->orderBy('created_at', 'desc');
        } else {
            // all
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate($perPage);

        $pendingCount = Order::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->count();

        return [
            'paginator' => $orders,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Get single order detail ensuring ownership.
     */
    public function getOrderDetail(Seller $seller, int $orderId): Order
    {
        return Order::where('seller_id', $seller->id)
            ->where('id', $orderId)
            ->with(['user', 'rider', 'items.shopProduct', 'items.product', 'items.globalProduct'])
            ->withCount(['messages as unread_messages' => fn ($q) => $q->where('sender_type', 'user')->where('is_read', false)])
            ->firstOrFail();
    }

    /**
     * Confirm a pending order and atomically reserve catalog inventory.
     */
    public function confirmOrder(Seller $seller, int $orderId): Order
    {
        $order = DB::transaction(function () use ($seller, $orderId) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::with('items')
                ->where('seller_id', $seller->id)
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => ["Order cannot be confirmed because it is currently in '{$lockedOrder->status}' status."],
                ]);
            }

            if (! $lockedOrder->inventory_reserved_at) {
                $this->reserveCatalogInventory($lockedOrder);
                $lockedOrder->inventory_reserved_at = now();
            }

            $lockedOrder->status = 'confirmed_by_seller';
            $lockedOrder->save();

            return $lockedOrder;
        });

        // Notifications
        try {
            $order->load(['user', 'rider', 'items.shopProduct', 'items.product', 'items.globalProduct']);
            $order->user?->notify(new OrderStatusNotification($order, 'confirmed_by_seller'));
        } catch (\Throwable $e) {
            Log::warning('Customer notification failed on order confirm', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Mark an order as preparing.
     */
    public function prepareOrder(Seller $seller, int $orderId): Order
    {
        $order = DB::transaction(function () use ($seller, $orderId) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::where('seller_id', $seller->id)
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'confirmed_by_seller') {
                throw ValidationException::withMessages([
                    'status' => ["Order cannot be marked as preparing from '{$lockedOrder->status}' status."],
                ]);
            }

            $lockedOrder->status = 'preparing';
            $lockedOrder->save();

            return $lockedOrder;
        });

        // Notifications
        try {
            $order->load(['user', 'rider', 'items.shopProduct', 'items.product', 'items.globalProduct']);
            $order->user?->notify(new OrderStatusNotification($order, 'preparing'));
        } catch (\Throwable $e) {
            Log::warning('Customer notification failed on order prepare', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Mark an order as ready for pickup and dispatch broadcast.
     */
    public function readyOrder(Seller $seller, int $orderId): Order
    {
        $order = DB::transaction(function () use ($seller, $orderId) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::where('seller_id', $seller->id)
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedOrder->status, ['preparing', 'confirmed_by_seller'], true)) {
                throw ValidationException::withMessages([
                    'status' => ["Order cannot be marked ready for pickup from '{$lockedOrder->status}' status."],
                ]);
            }

            $lockedOrder->status = 'ready_for_pickup';
            $lockedOrder->save();

            return $lockedOrder;
        });

        $order->load(['user', 'rider', 'seller', 'items.shopProduct', 'items.product', 'items.globalProduct']);

        // Dispatch broadcast event wrapped in try-catch to protect against connection/pusher errors
        try {
            event(new OrderReadyForPickup($order));
        } catch (\Throwable $e) {
            Log::warning('OrderReadyForPickup broadcast failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Notification
        try {
            $order->user?->notify(new OrderStatusNotification($order, 'ready_for_pickup'));
        } catch (\Throwable $e) {
            Log::warning('Customer notification failed on order ready', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Reject an order and restore catalog inventory if previously reserved.
     */
    public function rejectOrder(Seller $seller, int $orderId, ?string $reason): Order
    {
        $order = DB::transaction(function () use ($seller, $orderId, $reason) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::with('items')
                ->where('seller_id', $seller->id)
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedOrder->status, ['pending', 'confirmed_by_seller', 'preparing'], true)) {
                throw ValidationException::withMessages([
                    'status' => ["Order cannot be rejected because it is in '{$lockedOrder->status}' status."],
                ]);
            }

            if ($lockedOrder->inventory_reserved_at) {
                $this->restoreCatalogInventory($lockedOrder);
                $lockedOrder->inventory_reserved_at = null;
            }

            $lockedOrder->cancellation_reason = $reason ?? 'Rejected by seller';
            $lockedOrder->status = 'rejected';
            $lockedOrder->cancelled_at = now();
            $lockedOrder->save();

            return $lockedOrder;
        });

        // Load relationships
        $order->load(['user', 'rider', 'items.shopProduct', 'items.product', 'items.globalProduct']);

        // Notification
        try {
            $order->user?->notify(new OrderStatusNotification($order, 'rejected'));
        } catch (\Throwable $e) {
            Log::warning('Customer notification failed on order reject', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Cancellation email
        try {
            if ($order->user?->email) {
                Mail::to($order->user->email)->send(new OrderCancelledMail($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order reject email failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Mark a delivered order as completed.
     */
    public function completeOrder(Seller $seller, int $orderId): Order
    {
        $order = DB::transaction(function () use ($seller, $orderId) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::where('seller_id', $seller->id)
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'delivered') {
                throw ValidationException::withMessages([
                    'status' => ["Order cannot be completed from '{$lockedOrder->status}' status. It must be delivered first."],
                ]);
            }

            $lockedOrder->status = 'completed';
            $lockedOrder->save();

            return $lockedOrder;
        });

        $order->load(['user', 'rider', 'items.shopProduct', 'items.product', 'items.globalProduct']);

        // Notification
        try {
            $order->user?->notify(new OrderStatusNotification($order, 'completed'));
        } catch (\Throwable $e) {
            Log::warning('Customer notification failed on order completion', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Update operating hours and online status for seller.
     */
    public function updateOperatingHours(Seller $seller, array $data): Seller
    {
        $seller->is_open = (bool) $data['is_open'];
        $seller->opens_at = $data['opens_at'] ?? null;
        $seller->closes_at = $data['closes_at'] ?? null;
        $seller->save();

        return $seller->fresh();
    }

    /**
     * Calculate seller earnings for different periods and 6-month chart.
     */
    public function getEarnings(Seller $seller): array
    {
        $baseQuery = Order::where('seller_id', $seller->id)
            ->whereIn('status', ['delivered', 'completed']);

        $today = (clone $baseQuery)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $thisWeek = (clone $baseQuery)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_amount');

        $thisMonth = (clone $baseQuery)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');

        $thisYear = (clone $baseQuery)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');

        $allTime = (clone $baseQuery)->sum('total_amount');
        $totalCompletedOrders = (clone $baseQuery)->count();

        // 6-month chart (from 5 months ago to this month, oldest to newest)
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthOrders = (clone $baseQuery)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month);

            $monthlyChart[] = [
                'month_name' => $monthDate->format('M Y'),
                'order_count' => (int) $monthOrders->count(),
                'total_sales' => number_format((float) $monthOrders->sum('total_amount'), 2, '.', ''),
            ];
        }

        return [
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'this_year' => $thisYear,
            'all_time' => $allTime,
            'total_completed_orders' => $totalCompletedOrders,
            'monthly_chart' => $monthlyChart,
        ];
    }

    /**
     * Atomic catalog inventory reservation with row locking.
     */
    private function reserveCatalogInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->shop_product_id) {
                continue; // Skip legacy items matching web OrderController:731-733
            }

            /** @var ShopProduct|null $shopProduct */
            $shopProduct = ShopProduct::whereKey($item->shop_product_id)
                ->lockForUpdate()
                ->first();

            if (! $shopProduct || $shopProduct->stock_quantity < $item->quantity) {
                throw ValidationException::withMessages([
                    'stock' => ["{$item->item_name} does not have enough stock to confirm this order."],
                ]);
            }

            $shopProduct->decrement('stock_quantity', $item->quantity);
        }
    }

    /**
     * Restore catalog inventory on rejection or cancellation.
     */
    private function restoreCatalogInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->shop_product_id) {
                continue;
            }

            ShopProduct::whereKey($item->shop_product_id)
                ->increment('stock_quantity', $item->quantity);
        }
    }
}
