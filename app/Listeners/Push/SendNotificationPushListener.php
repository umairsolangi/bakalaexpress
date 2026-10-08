<?php

namespace App\Listeners\Push;

use App\Models\Order;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use App\Services\Api\PushService;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class SendNotificationPushListener
{
    public function __construct(
        protected PushService $pushService
    ) {}

    /**
     * Handle the event when a notification has been sent.
     */
    public function handle(NotificationSent $event): void
    {
        // Act only when notification is sent through the database channel to prevent duplicate pushes from mail
        if ($event->channel !== 'database') {
            return;
        }

        try {
            $notification = $event->notification;
            $notifiable = $event->notifiable;

            if ($notification instanceof NewOrderNotification) {
                $this->handleNewOrder($notification, $notifiable);
            } elseif ($notification instanceof OrderStatusNotification) {
                $this->handleOrderStatus($notification, $notifiable);
            }
        } catch (\Throwable $e) {
            Log::warning('SendNotificationPushListener error', ['error' => $e->getMessage()]);
        }
    }

    protected function handleNewOrder(NewOrderNotification $notification, mixed $seller): void
    {
        $dbData = $notification->toDatabase($seller);
        $orderId = (string) ($dbData['order_id'] ?? '');

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        $itemCount = 1;
        $amount = '';

        if ($order) {
            $itemCount = $order->items()->count() ?: 1;
            $amount = (string) $order->total_amount;
        }

        $title = "New order #{$orderId}";
        $body = $amount ? "{$itemCount} items, Rs {$amount}" : "New order received.";

        $this->pushService->sendToAccount($seller, $title, $body, [
            'type' => 'new_order',
            'order_id' => $orderId,
            'role' => 'seller',
        ]);
    }

    protected function handleOrderStatus(OrderStatusNotification $notification, mixed $customer): void
    {
        $dbData = $notification->toDatabase($customer);
        $orderId = (string) ($dbData['order_id'] ?? '');
        $status = (string) ($dbData['status'] ?? '');

        if (!$orderId || !$status) {
            return;
        }

        $order = Order::with('rider')->find($orderId);
        $riderFirstName = 'Rider';
        if ($order && $order->rider) {
            $riderFirstName = explode(' ', trim($order->rider->name))[0];
        }

        $statusText = match ($status) {
            'confirmed_by_seller' => 'Order confirmed',
            'preparing' => 'Being prepared',
            'ready_for_pickup' => 'Ready for pickup',
            'assigned_to_rider' => "Rider assigned: {$riderFirstName}",
            'picked_up' => 'On the way',
            'delivered' => 'Delivered',
            'cancelled' => 'Order cancelled',
            'rejected' => 'Order rejected',
            default => 'Order status updated',
        };

        $title = "Order #{$orderId}";
        $body = $statusText;

        $this->pushService->sendToAccount($customer, $title, $body, [
            'type' => 'order_status',
            'order_id' => $orderId,
            'status' => $status,
            'role' => 'customer',
        ]);
    }
}
