<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\OrderStatusNotification;

class RiderController extends Controller
{
    public function dashboard()
    {
        $rider = Auth::guard('rider')->user();

        $activeOrders = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned_to_rider', 'picked_up'])
            ->with(['seller', 'user', 'items.product', 'items.globalProduct', 'items.shopProduct'])
            ->latest()
            ->get();

        $availableOrders = Order::where('status', 'ready_for_pickup')
            ->whereNull('rider_id')
            ->with(['seller', 'items.product', 'items.globalProduct', 'items.shopProduct'])
            ->latest()
            ->get();

        $todayDeliveredCount = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $currentEarnings = $activeOrders->sum('delivery_charges');

        return view('rider.dashboard', compact(
            'rider',
            'activeOrders',
            'availableOrders',
            'todayDeliveredCount',
            'currentEarnings'
        ));
    }

    public function toggleStatus(Request $request)
    {
        $rider = Auth::guard('rider')->user();
        $rider->status = $rider->status === 'online' ? 'offline' : 'online';
        $rider->save();

        return back()->with('success', 'Status updated to ' . $rider->status);
    }

    public function acceptOrder($orderId)
    {
        $rider = Auth::guard('rider')->user();

        if (!$rider || !$rider->is_approved) {
            abort(403, 'Your rider account is not approved.');
        }

        $updatedRows = Order::where('id', $orderId)
            ->where('status', 'ready_for_pickup')
            ->whereNull('rider_id')
            ->update([
                'rider_id' => $rider->id,
                'status' => 'assigned_to_rider',
            ]);

        if ($updatedRows === 0) {
            return back()->with('error', 'Order is no longer available for pickup.');
        }

        // Notify the customer that a rider has been assigned
        $order = Order::with('user')->find($orderId);
        if ($order && $order->user) {
            $order->user->notify(new OrderStatusNotification($order, 'assigned_to_rider', $rider->name));
        }

        return back()->with('success', 'Order accepted successfully!');
    }

    public function updateStatus(Request $request, $orderId)
    {
        $rider = Auth::guard('rider')->user();
        $order = Order::findOrFail($orderId);

        if ($order->rider_id !== $rider->id) {
            abort(403);
        }

        $newStatus = $request->input('status');

        $allowedStatuses = ['picked_up', 'delivered'];

        if (!in_array($newStatus, $allowedStatuses)) {
            return back()->with('error', 'Invalid status update.');
        }

        if ($newStatus === 'delivered') {
            $request->validate([
                'delivery_proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            ]);

            if ($request->hasFile('delivery_proof_image')) {
                $order->delivery_proof_image = $request->file('delivery_proof_image')->store('delivery_proofs', 'public');
            }
        }

        $order->status = $newStatus;
        if ($newStatus === 'delivered' && !$order->estimated_delivery_at) {
            $order->estimated_delivery_at = now();
        }
        $order->save();

        // Notify the customer about the status change
        $order->load('user');
        if ($order->user) {
            $order->user->notify(new OrderStatusNotification($order, $newStatus, $rider->name));
        }

        return back()->with('success', 'Order status updated to ' . $newStatus);
    }

    public function availableOrders()
    {
        // API endpoint for fetching orders via AJAX if needed
        $orders = Order::where('status', 'ready_for_pickup')
            ->whereNull('rider_id')
            ->with('seller') // Include seller info for location
            ->get();

        return response()->json($orders);
    }
}
