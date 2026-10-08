<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApplyPromoRequest;
use App\Http\Requests\Api\CancelOrderRequest;
use App\Http\Requests\Api\CartValidateRequest;
use App\Http\Requests\Api\OrderFeedbackRequest;
use App\Http\Requests\Api\PlaceOrderRequest;
use App\Http\Resources\Api\OrderDetailResource;
use App\Http\Resources\Api\OrderListResource;
use App\Models\Order;
use App\Services\Api\ApiResponse;
use App\Services\Api\CustomerOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(
        protected CustomerOrderService $orderService
    ) {}

    /**
     * 1. POST /api/v1/customer/cart/validate
     */
    public function validateCart(CartValidateRequest $request): JsonResponse
    {
        $result = $this->orderService->validateCartItems($request->validated('items'));

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return ApiResponse::success(
            [
                'is_valid' => $result['is_valid'],
                'items' => $result['items'],
                'totals' => $result['totals'],
            ],
            'Cart validation completed.'
        );
    }

    /**
     * 2. POST /api/v1/customer/checkout/apply-promo
     */
    public function applyPromo(ApplyPromoRequest $request): JsonResponse
    {
        $result = $this->orderService->applyPromo(
            $request->validated('items'),
            $request->validated('code')
        );

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return ApiResponse::success(
            $result,
            'Promo code applied successfully.'
        );
    }

    /**
     * 3. POST /api/v1/customer/orders
     */
    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        if ($request->input('payment_method') !== 'cod') {
            return ApiResponse::error(
                'Only Cash on Delivery (cod) is supported in this stage.',
                'PAYMENT_METHOD_UNSUPPORTED',
                [],
                422
            );
        }

        $idempotencyKey = (string) $request->header('Idempotency-Key');

        return $this->orderService->placeOrder(
            $request->user(),
            $request->validated(),
            $idempotencyKey
        );
    }

    /**
     * 4. GET /api/v1/customer/orders/active
     */
    public function activeOrders(Request $request): JsonResponse
    {
        $perPage = min(30, max(1, $request->integer('per_page', 15)));
        $activeStatuses = config('bakala_orders.active_statuses', [
            'pending',
            'confirmed_by_seller',
            'preparing',
            'ready_for_pickup',
            'assigned_to_rider',
            'picked_up',
        ]);

        $paginator = Order::with(['seller', 'items'])
            ->withCount(['messages as unread_messages' => fn ($q) => $q->where('sender_type', 'seller')->where('is_read', false)])
            ->where('user_id', $request->user()->id)
            ->whereIn('status', $activeStatuses)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return ApiResponse::success(
            OrderListResource::collection($paginator->items()),
            'Active orders retrieved successfully.',
            $meta
        );
    }

    /**
     * 5. GET /api/v1/customer/orders/history
     */
    public function orderHistory(Request $request): JsonResponse
    {
        $perPage = min(30, max(1, $request->integer('per_page', 15)));
        $historyStatuses = config('bakala_orders.history_statuses', [
            'delivered',
            'completed',
            'cancelled',
            'rejected',
        ]);

        $paginator = Order::with(['seller', 'items'])
            ->withCount(['messages as unread_messages' => fn ($q) => $q->where('sender_type', 'seller')->where('is_read', false)])
            ->where('user_id', $request->user()->id)
            ->whereIn('status', $historyStatuses)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return ApiResponse::success(
            OrderListResource::collection($paginator->items()),
            'Order history retrieved successfully.',
            $meta
        );
    }

    /**
     * 6. GET /api/v1/customer/orders/{order}
     */
    public function show(Request $request, int $order): JsonResponse
    {
        $foundOrder = Order::with(['seller', 'items', 'rider'])
            ->withCount(['messages as unread_messages' => fn ($q) => $q->where('sender_type', 'seller')->where('is_read', false)])
            ->where('user_id', $request->user()->id)
            ->find($order);

        if (!$foundOrder) {
            return ApiResponse::error('Order not found.', 'NOT_FOUND', [], 404);
        }

        return ApiResponse::success(
            new OrderDetailResource($foundOrder),
            'Order details retrieved successfully.'
        );
    }

    /**
     * 7. POST /api/v1/customer/orders/{order}/cancel
     */
    public function cancel(CancelOrderRequest $request, int $order): JsonResponse
    {
        return $this->orderService->cancelOrder(
            $request->user(),
            $order,
            $request->input('reason')
        );
    }

    /**
     * 8. POST /api/v1/customer/orders/{order}/feedback
     */
    public function feedback(OrderFeedbackRequest $request, int $order): JsonResponse
    {
        return $this->orderService->submitFeedback(
            $request->user(),
            $order,
            $request->integer('rating'),
            (string) $request->input('feedback')
        );
    }

    /**
     * 9. GET /api/v1/customer/orders/{order}/reorder
     */
    public function reorder(Request $request, int $order): JsonResponse
    {
        return $this->orderService->buildReorderSummary($request->user(), $order);
    }
}
