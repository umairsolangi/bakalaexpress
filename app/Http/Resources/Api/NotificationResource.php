<?php

namespace App\Http\Resources\Api;

use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];
        $rawType = $this->type ?? '';

        $cleanType = 'other';
        $orderId = isset($data['order_id']) ? (string) $data['order_id'] : null;
        $status = isset($data['status']) ? (string) $data['status'] : null;

        if ($rawType === OrderStatusNotification::class || str_contains($rawType, 'OrderStatusNotification') || isset($data['status'])) {
            $cleanType = 'order_status';
            $title = $orderId ? "Order #{$orderId}" : 'Order Status Update';
            $body = $data['message'] ?? 'Your order status has been updated.';
        } elseif ($rawType === NewOrderNotification::class || str_contains($rawType, 'NewOrderNotification') || isset($data['order_details'])) {
            $cleanType = 'new_order';
            $title = $orderId ? "New order #{$orderId}" : 'New Order';
            $sellerName = $data['seller_name'] ?? 'Seller';
            $body = "New order received for {$sellerName}.";
        } else {
            $cleanType = 'other';
            $title = $data['title'] ?? 'Notification';
            $body = $data['message'] ?? ($data['body'] ?? 'You have a new notification.');
        }

        return [
            'id' => (string) $this->id,
            'type' => $cleanType,
            'title' => $title,
            'body' => $body,
            'order_id' => $orderId,
            'status' => $status,
            'created_at' => $this->created_at?->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
        ];
    }
}
