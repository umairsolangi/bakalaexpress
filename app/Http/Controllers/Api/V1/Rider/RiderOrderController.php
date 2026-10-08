<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RiderDeliverOrderRequest;
use App\Http\Requests\Api\RiderHistoryRequest;
use App\Http\Requests\Api\RiderStatusRequest;
use App\Models\Rider;
use App\Services\Api\ApiResponse;
use App\Services\Api\RiderOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiderOrderController extends Controller
{
    public function __construct(
        protected RiderOrderService $orderService
    ) {}

    /**
     * Helper to get authenticated rider instance.
     */
    protected function rider(Request $request): Rider
    {
        /** @var Rider $rider */
        $rider = $request->user();
        return $rider;
    }

    /**
     * 1. GET /api/v1/rider/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        return $this->orderService->getDashboard($this->rider($request));
    }

    /**
     * 2. POST /api/v1/rider/status
     */
    public function updateStatus(RiderStatusRequest $request): JsonResponse
    {
        $targetStatus = (string) $request->validated('status');
        return $this->orderService->updateStatus($this->rider($request), $targetStatus);
    }

    /**
     * 3. GET /api/v1/rider/orders/available
     */
    public function availableOrders(Request $request): JsonResponse
    {
        $perPage = min(30, max(1, $request->integer('per_page', 15)));
        return $this->orderService->getAvailableOrders($this->rider($request), $perPage);
    }

    /**
     * 4. GET /api/v1/rider/orders/current
     */
    public function currentOrders(Request $request): JsonResponse
    {
        return $this->orderService->getCurrentOrders($this->rider($request));
    }

    /**
     * 5. GET /api/v1/rider/orders/{order}
     */
    public function show(Request $request, int $order): JsonResponse
    {
        return $this->orderService->getOrderDetail($this->rider($request), $order);
    }

    /**
     * 6. POST /api/v1/rider/orders/{order}/accept
     */
    public function accept(Request $request, int $order): JsonResponse
    {
        return $this->orderService->acceptOrder($this->rider($request), $order);
    }

    /**
     * 7. POST /api/v1/rider/orders/{order}/pickup
     */
    public function pickup(Request $request, int $order): JsonResponse
    {
        return $this->orderService->pickupOrder($this->rider($request), $order);
    }

    /**
     * 8. POST /api/v1/rider/orders/{order}/deliver
     */
    public function deliver(RiderDeliverOrderRequest $request, int $order): JsonResponse
    {
        $proofFile = $request->file('proof_image');
        return $this->orderService->deliverOrder($this->rider($request), $order, $proofFile);
    }

    /**
     * 9. GET /api/v1/rider/history
     */
    public function history(RiderHistoryRequest $request): JsonResponse
    {
        $perPage = min(30, max(1, $request->integer('per_page', 15)));
        $from = $request->validated('from');
        $to = $request->validated('to');

        return $this->orderService->getHistory($this->rider($request), $from, $to, $perPage);
    }
}
