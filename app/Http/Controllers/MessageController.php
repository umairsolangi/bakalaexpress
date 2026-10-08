<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function chat(Order $order)
    {
        // Determine if user is seller or buyer based on the route being accessed
        $isSeller = request()->is('seller/chat/*');
        
        // Verify access based on route type
        if ($isSeller) {
            if (!Auth::guard('seller')->check() || Auth::guard('seller')->id() != $order->seller_id) {
                return redirect()->route('home')->with('error', 'Unauthorized access');
            }
        } else {
            if (!Auth::check() || Auth::id() != $order->user_id) {
                return redirect()->route('home')->with('error', 'Unauthorized access');
            }
        }
        
        // Get all messages for this order
        $messages = Message::where('order_id', $order->id)
                         ->orderBy('created_at', 'asc')
                         ->get();
        
        // Different back URLs for buyer and seller
        $backUrl = $isSeller 
            ? route('seller.order.handle', $order->id) // Seller back URL
            : route('order.track', $order->id);        // Buyer back URL
        
        return view('chat.index', compact('order', 'messages', 'isSeller', 'backUrl'));
    }
    
    public function send(Request $request, Order $order)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
        
        // Determine if sender is user or seller based on the route being accessed
        $isSeller = request()->is('seller/chat/*');
        
        // Verify access and get correct sender ID
        if ($isSeller) {
            if (!Auth::guard('seller')->check() || Auth::guard('seller')->id() != $order->seller_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $senderId = Auth::guard('seller')->id();
            $senderType = 'seller';
        } else {
            if (!Auth::check() || Auth::id() != $order->user_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $senderId = Auth::id();
            $senderType = 'user';
        }
        
        // Create the message
        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $request->message,
        ]);
        
        // We're not using real-time broadcasting anymore, so we comment this out
        // event(new NewMessage($message, $order));
        
        // Check if it's a traditional form submission or Ajax
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        
        // If it's a traditional form submission, redirect back to the chat
        return redirect()->back();
    }
    
    public function markAsRead(Order $order)
    {
        // Determine if user is seller or buyer based on the route being accessed
        $isSeller = request()->is('seller/chat/*');
        $senderType = $isSeller ? 'seller' : 'user';
        
        // Mark all messages as read where the authenticated user is not the sender
        Message::where('order_id', $order->id)
              ->where('sender_type', '!=', $senderType)
              ->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Get new messages for an order since last message ID
     * Used for polling fallback when WebSocket connection fails
     */
    public function getMessages(Order $order, Request $request)
    {
        // Verify access based on route type
        $isSeller = request()->is('seller/chat/*');
        
        if ($isSeller) {
            if (!Auth::guard('seller')->check() || Auth::guard('seller')->id() != $order->seller_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            if (!Auth::check() || Auth::id() != $order->user_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }
        
        // Accept an optional ?since=<id> parameter.
        // Only messages with id > since are returned, so the client never receives duplicates.
        $sinceId = (int) $request->query('since', 0);

        $messages = Message::where('order_id', $order->id)
                        ->where('id', '>', $sinceId)
                        ->orderBy('id', 'asc')
                        ->get();
        
        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
