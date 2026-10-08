<?php

namespace App\Services\Api;

use App\Models\Message;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OrderChatService
{
    public function __construct(
        protected PushService $pushService
    ) {}

    /**
     * Find order ensuring ownership by the caller role.
     */
    public function findOrderForUser(Model $user, string $role, int $orderId): ?Order
    {
        $query = Order::where('id', $orderId)->with(['user', 'seller']);

        if ($role === 'seller') {
            $query->where('seller_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->first();
    }

    /**
     * Determine if order is active.
     */
    public function isOrderActive(Order $order): bool
    {
        $activeStatuses = config('bakala_orders.active_statuses', [
            'pending',
            'confirmed_by_seller',
            'preparing',
            'ready_for_pickup',
            'assigned_to_rider',
            'picked_up',
        ]);

        return in_array((string) $order->status, $activeStatuses, true);
    }

    /**
     * Determine if chat is closed.
     */
    public function isChatClosed(Order $order): bool
    {
        if ($this->isOrderActive($order)) {
            return false;
        }

        $closeHours = (int) config('bakala_orders.chat_open_hours_after_close', 48);

        if (!$order->updated_at) {
            return false;
        }

        return $order->updated_at->copy()->addHours($closeHours)->isPast();
    }

    /**
     * List messages for an order.
     */
    public function getMessages(Model $user, string $role, Order $order, ?int $sinceId, int $limit): array
    {
        $limit = min(50, max(1, $limit));

        if ($sinceId !== null && $sinceId > 0) {
            $messages = Message::where('order_id', $order->id)
                ->where('id', '>', $sinceId)
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get();
        } else {
            // Latest messages, oldest first
            $messages = Message::where('order_id', $order->id)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();
        }

        // Attach order relation so resource doesn't re-query
        $order->loadMissing(['user', 'seller']);
        foreach ($messages as $msg) {
            $msg->setRelation('order', $order);
        }

        $mySenderType = ($role === 'seller') ? 'seller' : 'user';
        $otherSenderType = ($role === 'seller') ? 'user' : 'seller';

        $unreadCount = Message::where('order_id', $order->id)
            ->where('sender_type', $otherSenderType)
            ->where('is_read', false)
            ->count();

        $lastId = $messages->last()?->id;
        $isActive = $this->isOrderActive($order);

        return [
            'messages' => $messages,
            'meta' => [
                'unread_count' => (int) $unreadCount,
                'last_id' => $lastId ? (int) $lastId : null,
                'server_time' => now()->toIso8601String(),
                'suggested_poll_seconds' => $isActive ? 5 : 30,
            ],
        ];
    }

    /**
     * Send a message to the order chat.
     */
    public function sendMessage(Model $user, string $role, Order $order, string $messageText): Message|JsonResponse
    {
        // Rate limit: 20 per minute per user and order
        $rateKey = "order_chat_send:{$role}:{$user->id}:{$order->id}";
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return ApiResponse::error(
                "Too many messages sent. Please wait {$seconds} seconds before trying again.",
                'RATE_LIMIT_EXCEEDED',
                ['retry_after' => $seconds],
                429
            );
        }

        // Check if chat is closed
        if ($this->isChatClosed($order)) {
            return ApiResponse::error(
                'Chat is closed for this order.',
                'CHAT_CLOSED',
                [],
                422
            );
        }

        RateLimiter::hit($rateKey, 60);

        $senderType = ($role === 'seller') ? 'seller' : 'user';

        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message' => $messageText,
            'is_read' => false,
        ]);

        $order->loadMissing(['user', 'seller']);
        $message->setRelation('order', $order);

        // Send push notification to the other party
        try {
            /** @var Model|null $recipient */
            $recipient = ($role === 'seller') ? $order->user : $order->seller;
            if ($recipient) {
                $chatPreview = (bool) config('push.chat_preview', true);
                $title = "New message, order #{$order->id}";
                $body = $chatPreview ? mb_substr($messageText, 0, 60) : 'You have a new message';
                $data = [
                    'type' => 'chat_message',
                    'order_id' => (string) $order->id,
                    'role' => ($role === 'customer' ? 'seller' : 'customer'),
                ];

                $this->pushService->sendToAccount($recipient, $title, $body, $data, 30);
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification failed on chat message', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $message;
    }

    /**
     * Mark only the other party's unread messages as read.
     */
    public function markAsRead(Model $user, string $role, Order $order): array
    {
        $otherSenderType = ($role === 'seller') ? 'user' : 'seller';

        Message::where('order_id', $order->id)
            ->where('sender_type', $otherSenderType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return [
            'unread_count' => 0,
        ];
    }
}
