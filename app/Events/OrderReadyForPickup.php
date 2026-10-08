<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderReadyForPickup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast on a public channel 'riders' for now (simplicity)
        // or a private channel 'riders' if we want to restrict to auth'd riders
        return [
            new PrivateChannel('riders'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->order->id,
            'seller_name' => $this->order->seller->name,
            'address' => $this->order->seller->address ?? $this->order->seller->area . ', ' . $this->order->seller->city, // Fallback
            'status' => $this->order->status,
            'created_at' => $this->order->created_at,
        ];
    }
}
