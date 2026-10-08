<?php

// Create with: php artisan make:event NewMessage
// app/Events/NewMessage.php

namespace App\Events;

use App\Models\Message;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $order;

    public function __construct(Message $message, Order $order)
    {
        $this->message = $message;
        $this->order = $order;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->order->id);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'order_id' => $this->message->order_id,
                'sender_id' => $this->message->sender_id,
                'sender_type' => $this->message->sender_type,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at->toDateTimeString(),
                'is_read' => $this->message->is_read,
            ]
        ];
    }
}