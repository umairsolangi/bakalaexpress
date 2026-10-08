<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    protected Order $order;
    protected string $newStatus;
    protected ?string $riderName;

    public function __construct(Order $order, string $newStatus, ?string $riderName = null)
    {
        $this->order = $order;
        $this->newStatus = $newStatus;
        $this->riderName = $riderName;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->newStatus,
            'message' => $this->getMessage(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    private function getMessage(): string
    {
        $orderId = $this->order->id;

        return match ($this->newStatus) {
            'confirmed_by_seller' => "Your order #{$orderId} has been confirmed by the seller! 🎉",
            'preparing' => "Your order #{$orderId} is being prepared! 🍳",
            'ready_for_pickup' => "Your order #{$orderId} is packed and ready for pickup! 📦",
            'assigned_to_rider' => "Rider {$this->riderName} has been assigned to your order #{$orderId}! 🏍️",
            'picked_up' => "Your order #{$orderId} has been picked up and is on its way! 🚚",
            'delivered' => "Your order #{$orderId} has been delivered! ✅",
            'completed' => "Your order #{$orderId} is complete. Thank you for shopping with Bakala Express! 🙏",
            'cancelled' => "Your order #{$orderId} has been cancelled." . ($this->order->cancellation_reason ? " Reason: {$this->order->cancellation_reason}" : ''),
            'rejected' => "Your order #{$orderId} has been rejected by the seller. ❌",
            default => "Your order #{$orderId} status has been updated to " . ucwords(str_replace('_', ' ', $this->newStatus)) . ".",
        };
    }

    private function getIcon(): string
    {
        return match ($this->newStatus) {
            'confirmed_by_seller' => 'fa-check-circle',
            'preparing' => 'fa-kitchen-set',
            'ready_for_pickup' => 'fa-box',
            'assigned_to_rider' => 'fa-motorcycle',
            'picked_up' => 'fa-truck-fast',
            'delivered' => 'fa-location-dot',
            'completed' => 'fa-flag-checkered',
            'cancelled' => 'fa-ban',
            'rejected' => 'fa-times-circle',
            default => 'fa-info-circle',
        };
    }

    private function getColor(): string
    {
        return match ($this->newStatus) {
            'confirmed_by_seller', 'preparing' => 'blue',
            'ready_for_pickup', 'assigned_to_rider', 'picked_up' => 'indigo',
            'delivered', 'completed' => 'green',
            'cancelled', 'rejected' => 'red',
            default => 'gray',
        };
    }
}
