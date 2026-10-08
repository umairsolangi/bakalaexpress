@extends('layouts.app')

@section('title', 'Order #' . $order->id . ' - Bakala Express')

@section('content')
    @php
        $prettyStatus = ucwords(str_replace('_', ' ', $order->status));

        $statusColor = match ($order->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'rejected', 'cancelled' => 'bg-red-100 text-red-800',
            'delivered', 'completed' => 'bg-green-100 text-green-800',
            default => 'bg-blue-100 text-blue-800',
        };
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold font-display text-gray-900">Order #{{ $order->id }}</h1>
                <p class="text-gray-500 mt-1">Placed {{ $order->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusColor }}">
                    {{ $prettyStatus }}
                </span>
                <a href="{{ route('order.track', $order->id) }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-lg bg-primary text-white font-semibold hover:bg-primary-dark transition-colors">
                    <i class="fas fa-truck mr-2"></i> Track
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Summary</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Delivery address</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ $order->address }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Phone</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ $order->phone }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Seller</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ $order->seller?->name ?? 'Seller' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Payment</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">
                                {{ $order->transaction_id ? 'Online' : 'Cash on Delivery' }}
                            </div>
                            @if($order->transaction_id)
                                <div class="text-xs text-gray-500 mt-1">Txn: {{ $order->transaction_id }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                        <span class="text-sm text-gray-500">{{ $order->items?->count() ?? 0 }} item(s)</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->items as $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $item->item_name ?? $item->product?->name ?? $item->globalProduct?->name ?? 'Product' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700">
                                            {{ number_format($item->price) }} PKR
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                            {{ number_format($item->price * $item->quantity) }} PKR
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Total</td>
                                    <td class="px-6 py-4 text-right text-sm font-bold text-primary">
                                        {{ number_format($order->total_amount) }} PKR
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('order.track', $order->id) }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-gray-900 text-white font-semibold hover:bg-gray-800 transition-colors">
                            <i class="fas fa-location-crosshairs mr-2"></i> Track order
                        </a>

                        @if(in_array($order->status, ['confirmed_by_seller', 'preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'delivered', 'completed']))
                            <a href="{{ route('chat.index', $order->id) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                                <i class="fas fa-comments mr-2"></i> Chat with seller
                            </a>
                        @endif

                        @if(in_array($order->status, ['delivered', 'completed']))
                            <a href="{{ route('order.feedback', $order->id) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-accent text-white font-semibold hover:bg-accent-hover transition-colors">
                                <i class="fas fa-comment mr-2"></i> Leave feedback
                            </a>
                        @endif

                        @if(in_array($order->status, ['pending', 'confirmed_by_seller']))
                            <form action="{{ route('order.cancel', $order) }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="text" name="reason" maxlength="255" placeholder="Cancellation reason"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors"
                                    onclick="return confirm('Cancel this order?')">
                                    <i class="fas fa-ban mr-2"></i> Cancel order
                                </button>
                            </form>
                            <p class="text-xs text-gray-500">Orders can be cancelled until the seller starts preparing or dispatches them.</p>
                        @endif

                        @if(in_array($order->status, ['delivered', 'completed']))
                            <form action="{{ route('order.buyAgain', $order) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-primary text-white font-semibold hover:bg-primary-dark transition-colors">
                                    <i class="fas fa-rotate-right mr-2"></i> Buy again
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('order.history') }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Back to history
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
