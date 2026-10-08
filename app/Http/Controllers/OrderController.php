<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusNotification;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderCancelledMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Feedback;
use App\Models\User;

class OrderController extends Controller
{

    public function feedback($id)
    {
        $order = Order::findOrFail($id);
        return view('order.feedbacks', compact('order'));
    }

    public function submitFeedback(Request $request, $orderId)
    {
        $request->validate([
            'feedback' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $order = Order::findOrFail($orderId);

        Feedback::create([
            'order_id' => $orderId,
            'user_id' => auth()->id(), // Get the logged-in user
            'seller_id' => $order->seller_id, // This should be the seller ID from the order
            'feedback' => $request->feedback,
            'rating' => $request->rating,
            'status' => 'pending',
        ]);

        return redirect()->route('order.history')->with('success', 'Feedback submitted successfully!');
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $order->load(['seller', 'items.product', 'items.globalProduct', 'items.shopProduct']);
        return view('orders.show', compact('order'));
    }
    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        // If no completed orders are found, check for delivered orders too (which should be considered complete)
        if ($orders->isEmpty()) {
            $orders = Order::where('user_id', Auth::id())
                ->where(function ($query) {
                    $query->where('status', 'completed')
                        ->orWhere('status', 'delivered');
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('order.history', compact('orders'));
    }

    public function allOrders()
    {
        $user = auth()->user();

        // Fetch all orders except those with status 'completed' for the authenticated user
        $orders = $user->orders()->where('status', '!=', 'completed')->get();

        return view('order.all-orders', compact('orders'));
    }


    public function showCheckout()
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty.');
        }

        $shopProductIds = collect($cart)->pluck('shop_product_id')->filter()->unique()->toArray();
        $listings = empty($shopProductIds)
            ? collect()
            : ShopProduct::with(['seller', 'globalProduct'])
                ->where('is_active', true)
                ->whereIn('id', $shopProductIds)
                ->get()
                ->keyBy('id');

        foreach ($cart as $key => $item) {
            if (!empty($item['shop_product_id'])) {
                $listing = $listings->get($item['shop_product_id']);

                if (!$listing || !$listing->globalProduct || !$listing->globalProduct->is_active || (int) $listing->stock_quantity < 1) {
                    return redirect()->route('cart.view')->with('error', 'Your cart has unavailable items. Please review it before checkout.');
                }

                $cart[$key]['name'] = $listing->globalProduct->name;
                $cart[$key]['price'] = $listing->effective_price;
                $cart[$key]['seller_name'] = $listing->seller?->name;
                $cart[$key]['stock_quantity'] = (int) $listing->stock_quantity;
                if ((int) $listing->stock_quantity < (int) ($item['quantity'] ?? 1)) {
                    return redirect()->route('cart.view')->with('error', 'Your cart quantity exceeds current stock. Please update it before checkout.');
                }
            }
        }

        Session::put('cart', $cart);

        $subtotal = collect($cart)->sum(function ($item) {
            return ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        });
        $promoCode = null;
        $discount = 0;
        if (session('checkout_promo_code')) {
            $promoCode = PromoCode::where('code', session('checkout_promo_code'))->first();
            if ($promoCode && $promoCode->isUsableFor($subtotal)) {
                $discount = $promoCode->discountFor($subtotal);
            } else {
                session()->forget('checkout_promo_code');
                $promoCode = null;
            }
        }
        $deliveryFee = $subtotal > 0 ? 0 : 0;
        $total = max(0, $subtotal + $deliveryFee - $discount);
        $estimatedDeliveryWindow = '30-45 minutes';
        $onlinePaymentsEnabled = (bool) config('services.payments.online_enabled', false);
        $user = auth()->user();
        $savedAddress = trim(collect([$user?->address, $user?->address2, $user?->city])->filter()->implode(', '));
        $savedPhone = $user?->mobile ?? '';

        return view('checkout.show', compact(
            'cart',
            'subtotal',
            'deliveryFee',
            'discount',
            'promoCode',
            'total',
            'estimatedDeliveryWindow',
            'onlinePaymentsEnabled',
            'savedAddress',
            'savedPhone'
        ));
    }

    public function placeOrder(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to place an order.');
        }

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Invalid cart data.');
        }

        if (!empty(reset($cart)['shop_product_id'])) {
            return $this->placeCatalogOrder($request, $cart);
        }

        return $this->placeLegacyOrder($request, $cart);
    }

    public function applyPromo(Request $request)
    {
        $validated = $request->validate([
            'promo_code' => 'nullable|string|max:50',
            'clear_promo' => 'nullable|boolean',
        ]);

        if ($request->boolean('clear_promo')) {
            session()->forget('checkout_promo_code');
            return redirect()->route('checkout.show')->with('success', 'Promo code removed.');
        }

        $code = strtoupper(trim((string) ($validated['promo_code'] ?? '')));
        if ($code === '') {
            return redirect()->route('checkout.show')->with('error', 'Enter a promo code.');
        }

        $subtotal = collect(session('cart', []))->sum(fn ($item) => ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 0)));
        $promoCode = PromoCode::where('code', $code)->first();

        if (!$promoCode || !$promoCode->isUsableFor($subtotal)) {
            return redirect()->route('checkout.show')->with('error', 'Promo code is invalid or not applicable.');
        }

        session(['checkout_promo_code' => $code]);
        return redirect()->route('checkout.show')->with('success', 'Promo code applied.');
    }

    private function placeCatalogOrder(Request $request, array $cart)
    {
        try {
            $request->validate([
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'payment_method' => 'required|in:cod,online',
                'transaction_id' => 'required_if:payment_method,online|nullable|string|max:255',
            ]);

            if (
                $request->payment_method === 'online' &&
                !config('services.payments.online_enabled', false)
            ) {
                return redirect()->route('checkout.show')->with('error', 'Online payment is currently unavailable. Please use Cash on Delivery.');
            }

            if ($request->filled('promo_code')) {
                session(['checkout_promo_code' => strtoupper(trim($request->promo_code))]);
            } elseif ($request->boolean('clear_promo')) {
                session()->forget('checkout_promo_code');
            }

            $validatedItems = [];
            $totalAmount = 0;
            $sellerId = null;

            // Bulk fetch listings to eliminate N+1 queries in loop
            $shopProductIds = collect($cart)->pluck('shop_product_id')->filter()->unique()->toArray();
            $listings = empty($shopProductIds)
                ? collect()
                : ShopProduct::with(['seller', 'globalProduct'])
                    ->where('is_active', true)
                    ->whereIn('id', $shopProductIds)
                    ->get()
                    ->keyBy('id');

            foreach ($cart as $cartItem) {
                $listing = $listings->get($cartItem['shop_product_id']);

                if (!$listing || !$listing->globalProduct || !$listing->globalProduct->is_active) {
                    return redirect()->route('cart.view')->with('error', 'One of the cart items is no longer available.');
                }

                if ($sellerId === null) {
                    $sellerId = (int) $listing->seller_id;
                }

                if ((int) $listing->seller_id !== $sellerId) {
                    return redirect()->route('cart.view')->with('error', 'All products must be from the same seller.');
                }

                $unitPrice = $listing->effective_price;
                $quantity = (int) ($cartItem['quantity'] ?? 0);
                if ($quantity < 1) {
                    return redirect()->route('cart.view')->with('error', 'Invalid item quantity in cart.');
                }

                if ((int) $listing->stock_quantity < $quantity) {
                    return redirect()->route('cart.view')->with('error', $listing->globalProduct->name . ' does not have enough stock.');
                }

                $totalAmount += $unitPrice * $quantity;

                $validatedItems[] = [
                    'shop_product_id' => $listing->id,
                    'global_product_id' => $listing->global_product_id,
                    'item_name' => $listing->globalProduct->name,
                    'unit_type' => $listing->globalProduct->unit_type,
                    'item_image' => $listing->globalProduct->default_image,
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                ];
            }

            $seller = Seller::find($sellerId);
            if (!$seller) {
                return redirect()->route('cart.view')->with('error', 'Invalid seller.');
            }

            if (!$seller->isAcceptingOrders()) {
                return redirect()->route('cart.view')->with('error', 'This seller is currently closed.');
            }

            $order = DB::transaction(function () use ($request, $seller, $totalAmount, $validatedItems) {
                // Fetch and lock the promo code inside the active transaction block
                $promoCode = session('checkout_promo_code')
                    ? PromoCode::where('code', session('checkout_promo_code'))->lockForUpdate()->first()
                    : null;
                $discountAmount = $promoCode && $promoCode->isUsableFor($totalAmount)
                    ? $promoCode->discountFor($totalAmount)
                    : 0;
                $payableTotal = max(0, $totalAmount - $discountAmount);

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => $seller->id,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'status' => 'pending',
                    'total_amount' => $payableTotal,
                    'delivery_charges' => 0.00,
                    'transaction_id' => $request->payment_method === 'online' ? $request->transaction_id : null,
                    'promo_code_id' => $promoCode?->id,
                    'discount_amount' => $discountAmount,
                    'estimated_delivery_at' => now()->addMinutes(45),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);

                foreach ($validatedItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'shop_product_id' => $item['shop_product_id'],
                        'global_product_id' => $item['global_product_id'],
                        'product_id' => null,
                        'item_name' => $item['item_name'],
                        'unit_type' => $item['unit_type'],
                        'item_image' => $item['item_image'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }

                if ($promoCode && $discountAmount > 0) {
                    $promoCode->increment('used_count');
                }

                return $order;
            });

            session()->forget('cart');
            session()->forget('checkout_promo_code');
            $seller->notify(new \App\Notifications\NewOrderNotification($seller->name, $order->id, json_encode($validatedItems)));

            // Send order confirmation email to customer
            try {
                $order->load(['items', 'seller', 'user']);
                if ($order->user?->email) {
                    Mail::to($order->user->email)->send(new OrderPlacedMail($order));
                }
            } catch (\Exception $e) {
                Log::warning('Order placed email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            return redirect()->route('home')->with('success', 'Order placed successfully.');

        } catch (\Exception $e) {
            Log::error('Catalog order placement failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('checkout.show')->with('error', 'Order placement failed.');
        }
    }

    private function placeLegacyOrder(Request $request, array $cart)
    {
        $validatedItems = [];
        $totalAmount = 0;

        // Bulk fetch legacy products to eliminate N+1 queries in loop
        $productIds = collect($cart)->pluck('id')->merge(collect($cart)->pluck('product_id'))->filter()->unique()->toArray();
        $products = empty($productIds)
            ? collect()
            : Product::with('seller')
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

        foreach ($cart as $item) {
            $prodId = $item['id'] ?? $item['product_id'] ?? null;
            $product = $prodId ? $products->get($prodId) : null;
            if (!$product) {
                Log::error('Invalid product in cart for legacy order placement', ['item' => $item]);
                return redirect()->route('home')->with('error', 'One of the items in your cart is invalid or unavailable.');
            }
            
            $unitPrice = (float) $product->price;
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($quantity < 1) {
                return redirect()->route('home')->with('error', 'Invalid item quantity in cart.');
            }

            $totalAmount += $unitPrice * $quantity;

            $validatedItems[] = [
                'product_id' => $product->id,
                'item_name' => $product->name,
                'quantity' => $quantity,
                'price' => $unitPrice,
                'seller_id' => $product->seller_id,
            ];
        }

        $seller = Seller::find($validatedItems[0]['seller_id']);
        if (!$seller) {
            Log::error('Invalid seller_id for order placement', ['seller_id' => $validatedItems[0]['seller_id']]);
            return redirect()->route('home')->with('error', 'Invalid seller.');
        }

        $sellerIds = collect($validatedItems)->pluck('seller_id')->unique();
        if ($sellerIds->count() > 1) {
            Log::error('Multiple sellers in cart not supported', ['seller_ids' => $sellerIds->toArray()]);
            return redirect()->route('home')->with('error', 'All products must be from the same seller.');
        }

        $itemsWithoutSeller = collect($validatedItems)->filter(function ($item) {
            return empty($item['seller_id']);
        });

        if ($itemsWithoutSeller->count() > 0) {
            Log::error('Cart items missing seller_id', ['items' => $itemsWithoutSeller->keys()->toArray()]);
            return redirect()->route('home')->with('error', 'Some cart items are invalid. Please refresh your cart.');
        }

        try {
            Log::info('Attempting to place a legacy order', [
                'user_id' => auth()->id(),
                'cart_count' => count($cart),
                'total_amount' => $totalAmount,
                'seller_id' => $seller->id,
                'seller_name' => $seller->name,
            ]);

            $request->validate([
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'payment_method' => 'required|in:cod,online',
                'transaction_id' => 'required_if:payment_method,online|nullable|string|max:255',
            ]);

            if (
                $request->payment_method === 'online' &&
                !config('services.payments.online_enabled', false)
            ) {
                return redirect()->route('checkout.show')->with('error', 'Online payment is currently unavailable. Please use Cash on Delivery.');
            }

            if (!$seller->isAcceptingOrders()) {
                return redirect()->route('cart.view')->with('error', 'This seller is currently closed.');
            }

            $order = DB::transaction(function () use ($request, $seller, $totalAmount, $validatedItems) {
                // Fetch and lock the promo code inside the active transaction block
                $promoCode = session('checkout_promo_code')
                    ? PromoCode::where('code', session('checkout_promo_code'))->lockForUpdate()->first()
                    : null;
                $discountAmount = $promoCode && $promoCode->isUsableFor($totalAmount)
                    ? $promoCode->discountFor($totalAmount)
                    : 0;
                $payableTotal = max(0, $totalAmount - $discountAmount);

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => $seller->id,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'status' => 'pending',
                    'total_amount' => $payableTotal,
                    'delivery_charges' => 0.00,
                    'transaction_id' => $request->payment_method === 'online' ? $request->transaction_id : null,
                    'promo_code_id' => $promoCode?->id,
                    'discount_amount' => $discountAmount,
                    'estimated_delivery_at' => now()->addMinutes(45),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);

                foreach ($validatedItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }

                if ($promoCode && $discountAmount > 0) {
                    $promoCode->increment('used_count');
                }

                return $order;
            });

            session()->forget('cart');
            session()->forget('checkout_promo_code');
            $seller->notify(new \App\Notifications\NewOrderNotification($seller->name, $order->id, json_encode($order->items)));

            // Send order confirmation email to customer
            try {
                $order->load(['items', 'seller', 'user']);
                if ($order->user?->email) {
                    Mail::to($order->user->email)->send(new OrderPlacedMail($order));
                }
            } catch (\Exception $e) {
                Log::warning('Order placed email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            return redirect()->route('home')->with('success', 'Order placed successfully.');
        } catch (\Exception $e) {
            Log::error('Error occurred during legacy order placement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('home')->with('error', 'Order placement failed.');
        }
    }

    public function buyAgain(Order $order)
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);

        $cart = [];
        $skipped = 0;
        $order->load('items.shopProduct.globalProduct', 'items.shopProduct.seller', 'items.product.seller');

        foreach ($order->items as $item) {
            if ($item->shop_product_id && $item->shopProduct && $item->shopProduct->is_active && (int) $item->shopProduct->stock_quantity > 0) {
                $listing = $item->shopProduct;
                $cart[(string) $listing->id] = [
                    'id' => $listing->id,
                    'shop_product_id' => $listing->id,
                    'global_product_id' => $listing->global_product_id,
                    'seller_id' => $listing->seller_id,
                    'seller_name' => $listing->seller?->name,
                    'name' => $listing->globalProduct?->name,
                    'price' => $listing->effective_price,
                    'unit_type' => $listing->globalProduct?->unit_type,
                    'quantity' => min((int) $item->quantity, (int) $listing->stock_quantity),
                    'stock_quantity' => (int) $listing->stock_quantity,
                ];
                continue;
            }

            if ($item->product_id && $item->product) {
                $product = $item->product;
                $cart[(string) $product->id] = [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'seller_name' => $product->seller?->name,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => (int) $item->quantity,
                ];
                continue;
            }

            $skipped++;
        }

        if ($cart === []) {
            return redirect()->route('order.history')->with('error', 'None of the items from that order are currently available.');
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.view')->with('success', 'Items added to cart.' . ($skipped ? " {$skipped} unavailable item(s) skipped." : ''));
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);

        if (!in_array($order->status, ['pending', 'confirmed_by_seller'], true)) {
            return back()->with('error', 'This order can no longer be cancelled from the app.');
        }

        $request->validate(['reason' => 'nullable|string|max:255']);
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        // Notify the customer about the cancellation
        $order->refresh();
        $user = $order->user;
        if ($user) {
            $user->notify(new OrderStatusNotification($order, 'cancelled'));
        }

        return back()->with('success', 'Order cancelled.');
    }

    private function getProduct($product_id)
    {
        return Product::find($product_id);
    }

    public function trackOrder(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        return view('order.track', compact('order'));
    }

    public function track(Order $order)
    {
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['user', 'seller', 'items.product', 'items.globalProduct', 'items.shopProduct']);

        return view('order.track', compact('order'));
    }


    public function acceptRejectOrder(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:confirmed_by_seller,rejected',
        ]);

        try {
            $this->applySellerStatusChange($order, $request->input('status'));
        } catch (\RuntimeException $e) {
            return redirect()->route('seller.panel')->with('error', $e->getMessage());
        }

        return redirect()->route('seller.panel')->with('success', 'Order status updated successfully.');
    }

    public function handleOrder(Order $order)
    {
        $sellerId = auth()->guard('seller')->id();
        if ($order->seller_id !== $sellerId) {
            return redirect()->route('seller.panel')->with('error', 'You do not have permission to manage this order.');
        }

        return view('seller.order-handle', compact('order'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:confirmed_by_seller,preparing,ready_for_pickup,assigned_to_rider,picked_up,delivered,completed,rejected',
        ]);

        $sellerId = auth()->guard('seller')->id();
        if ($order->seller_id !== $sellerId) {
            return redirect()->route('seller.panel')->with('error', 'You do not have permission to update this order.');
        }

        // Logic split for Rider integration
        // If status is ready_for_pickup, it might trigger rider assignment in future

        try {
            $this->applySellerStatusChange($order, $request->status);
        } catch (\RuntimeException $e) {
            return redirect()->route('seller.order.handle', $order)->with('error', $e->getMessage());
        }

        // Broadcast event if order is ready for pickup
        if ($request->status === 'ready_for_pickup') {
            event(new \App\Events\OrderReadyForPickup($order));
        }

        // If the order is marked as completed, update the seller's earnings
        if ($request->status === 'completed') {
            // Additional logic
        }

        return redirect()->route('seller.order.handle', $order)->with('success', 'Order status updated successfully!');
    }

    private function applySellerStatusChange(Order $order, string $status): void
    {
        DB::transaction(function () use ($order, $status) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($status === 'confirmed_by_seller' && !$lockedOrder->inventory_reserved_at) {
                $this->reserveCatalogInventory($lockedOrder);
                $lockedOrder->inventory_reserved_at = now();
            }

            if ($status === 'rejected' && $lockedOrder->inventory_reserved_at) {
                $this->restoreCatalogInventory($lockedOrder);
                $lockedOrder->inventory_reserved_at = null;
            }

            $lockedOrder->status = $status;
            $lockedOrder->save();
        });

        // Notify the customer about the status change
        $order->refresh();
        $user = $order->user;
        if ($user) {
            $user->notify(new OrderStatusNotification($order, $status));

            // Send email for key status transitions
            try {
                if ($user->email) {
                    if ($status === 'delivered') {
                        Mail::to($user->email)->send(new OrderDeliveredMail($order));
                    } elseif (in_array($status, ['cancelled', 'rejected'])) {
                        Mail::to($user->email)->send(new OrderCancelledMail($order));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Order status email failed', ['order_id' => $order->id, 'status' => $status, 'error' => $e->getMessage()]);
            }
        }
    }

    private function reserveCatalogInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            if (!$item->shop_product_id) {
                continue;
            }

            $updatedRows = ShopProduct::whereKey($item->shop_product_id)
                ->where('stock_quantity', '>=', $item->quantity)
                ->decrement('stock_quantity', $item->quantity);

            if ($updatedRows === 0) {
                throw new \RuntimeException($item->item_name . ' does not have enough stock to confirm this order.');
            }
        }
    }

    private function restoreCatalogInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            if (!$item->shop_product_id) {
                continue;
            }

            ShopProduct::whereKey($item->shop_product_id)
                ->increment('stock_quantity', $item->quantity);
        }
    }

    public function showOrderHandling(Order $order)
    {
        return view('seller.order-handle', compact('order'));
    }
}
