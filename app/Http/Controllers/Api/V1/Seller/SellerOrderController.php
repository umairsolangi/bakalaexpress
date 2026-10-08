<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SellerOperatingHoursRequest;
use App\Http\Requests\Api\SellerOrderListRequest;
use App\Http\Requests\Api\SellerRejectOrderRequest;
use App\Http\Resources\Api\SellerDashboardResource;
use App\Http\Resources\Api\SellerEarningsResource;
use App\Http\Resources\Api\SellerOrderDetailResource;
use App\Http\Resources\Api\SellerOrderShortResource;
use App\Http\Resources\Api\SellerOperatingHoursResource;
use App\Models\Seller;
use App\Services\Api\ApiResponse;
use App\Services\Api\SellerOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    protected SellerOrderService $orderService;

    public function __construct(SellerOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    private function seller(Request $request): Seller
    {
        /** @var Seller $seller */
        $seller = $request->user();
        return $seller;
    }

    /**
     * GET /api/v1/seller/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $stats = $this->orderService->getDashboard($seller);

        return ApiResponse::success(
            new SellerDashboardResource($seller, $stats),
            'Seller dashboard retrieved successfully.',
            [
                'server_time' => now()->toIso8601String(),
                'suggested_poll_seconds' => 15,
            ]
        );
    }

    /**
     * GET /api/v1/seller/orders
     */
    public function index(SellerOrderListRequest $request): JsonResponse
    {
        $seller = $this->seller($request);
        $result = $this->orderService->listOrders($seller, $request->validated());
        $paginator = $result['paginator'];

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'pending_count' => (int) $result['pending_count'],
            'server_time' => now()->toIso8601String(),
            'suggested_poll_seconds' => 15,
        ];

        return ApiResponse::success(
            SellerOrderShortResource::collection($paginator->items()),
            'Orders retrieved successfully.',
            $meta
        );
    }

    /**
     * GET /api/v1/seller/orders/{order}
     */
    public function show(Request $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        $orderModel = $this->orderService->getOrderDetail($seller, $order);

        return ApiResponse::success(
            new SellerOrderDetailResource($orderModel),
            'Order details retrieved successfully.'
        );
    }

    /**
     * POST /api/v1/seller/orders/{order}/confirm
     */
    public function confirm(Request $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        $confirmedOrder = $this->orderService->confirmOrder($seller, $order);

        return ApiResponse::success(
            new SellerOrderDetailResource($confirmedOrder),
            'Order confirmed successfully.'
        );
    }

    /**
     * POST /api/v1/seller/orders/{order}/prepare
     */
    public function prepare(Request $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        $preparedOrder = $this->orderService->prepareOrder($seller, $order);

        return ApiResponse::success(
            new SellerOrderDetailResource($preparedOrder),
            'Order is now being prepared.'
        );
    }

    /**
     * POST /api/v1/seller/orders/{order}/ready
     */
    public function ready(Request $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        $readyOrder = $this->orderService->readyOrder($seller, $order);

        return ApiResponse::success(
            new SellerOrderDetailResource($readyOrder),
            'Order marked ready for pickup.'
        );
    }

    /**
     * POST /api/v1/seller/orders/{order}/reject
     */
    public function reject(SellerRejectOrderRequest $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        $reason = $request->validated('reason');
        $rejectedOrder = $this->orderService->rejectOrder($seller, $order, $reason);

        return ApiResponse::success(
            new SellerOrderDetailResource($rejectedOrder),
            'Order rejected successfully.'
        );
    }

    /**
     * POST /api/v1/seller/orders/{order}/complete
     */
    public function complete(Request $request, int $order): JsonResponse
    {
        $seller = $this->seller($request);
        $completedOrder = $this->orderService->completeOrder($seller, $order);

        return ApiResponse::success(
            new SellerOrderDetailResource($completedOrder),
            'Order completed successfully.'
        );
    }

    /**
     * GET /api/v1/seller/operating-hours
     */
    public function getOperatingHours(Request $request): JsonResponse
    {
        $seller = $this->seller($request);

        return ApiResponse::success(
            new SellerOperatingHoursResource($seller),
            'Operating hours retrieved successfully.'
        );
    }

    /**
     * PUT /api/v1/seller/operating-hours
     */
    public function updateOperatingHours(SellerOperatingHoursRequest $request): JsonResponse
    {
        $seller = $this->seller($request);
        $updatedSeller = $this->orderService->updateOperatingHours($seller, $request->validated());

        return ApiResponse::success(
            new SellerOperatingHoursResource($updatedSeller),
            'Operating hours updated successfully.'
        );
    }

    /**
     * GET /api/v1/seller/earnings
     */
    public function earnings(Request $request): JsonResponse
    {
        $seller = $this->seller($request);
        $earningsData = $this->orderService->getEarnings($seller);

        return ApiResponse::success(
            new SellerEarningsResource($earningsData),
            'Earnings summary retrieved successfully.'
        );
    }
}
