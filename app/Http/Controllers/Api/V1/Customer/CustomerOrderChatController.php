<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendOrderMessageRequest;
use App\Http\Resources\Api\MessageResource;
use App\Services\Api\ApiResponse;
use App\Services\Api\OrderChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderChatController extends Controller
{
    public function __construct(
        protected OrderChatService $chatService
    ) {}

    /**
     * GET /api/v1/customer/orders/{order}/messages
     */
    public function index(Request $request, int $orderId): JsonResponse
    {
        $order = $this->chatService->findOrderForUser($request->user(), 'customer', $orderId);
        if (!$order) {
            return ApiResponse::error('Order not found.', 'ORDER_NOT_FOUND', [], 404);
        }

        $rawSince = $request->input('since_id') ?? $request->input('since');
        $sinceId = $rawSince !== null ? (int) $rawSince : null;
        $limit = $request->integer('limit', 30);

        $result = $this->chatService->getMessages($request->user(), 'customer', $order, $sinceId, $limit);

        return ApiResponse::success(
            MessageResource::collection($result['messages']),
            'Messages retrieved successfully.',
            $result['meta']
        );
    }

    /**
     * POST /api/v1/customer/orders/{order}/messages
     */
    public function send(SendOrderMessageRequest $request, int $orderId): JsonResponse
    {
        $order = $this->chatService->findOrderForUser($request->user(), 'customer', $orderId);
        if (!$order) {
            return ApiResponse::error('Order not found.', 'ORDER_NOT_FOUND', [], 404);
        }

        $result = $this->chatService->sendMessage(
            $request->user(),
            'customer',
            $order,
            $request->validated('message')
        );

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return ApiResponse::success(
            new MessageResource($result, 'customer'),
            'Message sent successfully.'
        );
    }

    /**
     * POST /api/v1/customer/orders/{order}/messages/read
     */
    public function markAsRead(Request $request, int $orderId): JsonResponse
    {
        $order = $this->chatService->findOrderForUser($request->user(), 'customer', $orderId);
        if (!$order) {
            return ApiResponse::error('Order not found.', 'ORDER_NOT_FOUND', [], 404);
        }

        $result = $this->chatService->markAsRead($request->user(), 'customer', $order);

        return ApiResponse::success(
            $result,
            'Messages marked as read.'
        );
    }
}
