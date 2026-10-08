<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NotificationMarkReadRequest;
use App\Http\Resources\Api\NotificationResource;
use App\Models\Seller;
use App\Services\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerNotificationController extends Controller
{
    /**
     * GET /api/v1/seller/notifications
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Seller $seller */
        $seller = $request->user();
        $perPage = min(30, max(1, $request->integer('per_page', 15)));

        $paginator = $seller->notifications()->latest()->paginate($perPage);

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'unread_count' => $seller->unreadNotifications()->count(),
        ];

        return ApiResponse::success(
            NotificationResource::collection($paginator->items()),
            'Notifications retrieved successfully.',
            $meta
        );
    }

    /**
     * POST /api/v1/seller/notifications/read
     */
    public function markAsRead(NotificationMarkReadRequest $request): JsonResponse
    {
        /** @var Seller $seller */
        $seller = $request->user();

        if ($request->boolean('all')) {
            $seller->unreadNotifications()->update(['read_at' => now()]);
        } elseif ($request->filled('ids')) {
            $ids = (array) $request->input('ids');
            $seller->unreadNotifications()
                ->whereIn('id', $ids)
                ->update(['read_at' => now()]);
        }

        $unreadCount = $seller->unreadNotifications()->count();

        return ApiResponse::success(
            ['unread_count' => $unreadCount],
            'Notifications marked as read.'
        );
    }
}
