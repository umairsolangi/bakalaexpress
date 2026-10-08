<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Channel for riders to receive new order notifications
Broadcast::channel('riders', function ($user) {
    // Check if the user is a rider
    return auth()->guard('rider')->check();
});

// Channel for order-specific chat messages
Broadcast::channel('chat.{orderId}', function ($user, $orderId) {
    $order = Order::findOrFail($orderId);

    // Allow access if user is the buyer
    if (auth()->check() && $user->id === $order->user_id) {
        return true;
    }

    // Allow access if user is the seller
    if (auth()->guard('seller')->check() && auth()->guard('seller')->id() === $order->seller_id) {
        return true;
    }

    // Allow access if user is the assigned rider
    if (auth()->guard('rider')->check() && auth()->guard('rider')->id() === $order->rider_id) {
        return true;
    }

    return false;
});
