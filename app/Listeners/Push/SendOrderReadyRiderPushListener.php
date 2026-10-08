<?php

namespace App\Listeners\Push;

use App\Events\OrderReadyForPickup;
use App\Models\Rider;
use App\Services\Api\PushService;
use Illuminate\Support\Facades\Log;

class SendOrderReadyRiderPushListener
{
    public function __construct(
        protected PushService $pushService
    ) {}

    /**
     * Handle the OrderReadyForPickup broadcast event.
     */
    public function handle(OrderReadyForPickup $event): void
    {
        try {
            $order = $event->order;
            $order->loadMissing('seller');

            $shopName = $order->seller?->name ?? 'Shop';
            $shopArea = $order->seller?->area ?? ($order->seller?->city ?? 'Nearby');

            $title = 'New order ready';
            $body = "Pickup from {$shopName}, {$shopArea}";

            $limit = (int) config('push.rider_broadcast_limit', 200);

            // Fetch online and approved riders up to limit
            $riders = Rider::where('status', 'online')
                ->where('is_approved', 1)
                ->limit($limit)
                ->get();

            if ($riders->isEmpty()) {
                return;
            }

            $this->pushService->sendToMany($riders, $title, $body, [
                'type' => 'order_ready',
                'order_id' => (string) $order->id,
                'role' => 'rider',
            ]);
        } catch (\Throwable $e) {
            Log::warning('SendOrderReadyRiderPushListener error', ['error' => $e->getMessage()]);
        }
    }
}
