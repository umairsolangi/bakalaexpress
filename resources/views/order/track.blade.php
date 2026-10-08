@extends('layouts.app')

@section('title', 'Track Order #' . $order->id . ' - Bakala Express')

@section('content')
    @php
        $prettyStatus = ucwords(str_replace('_', ' ', $order->status));

        $statusColor = match ($order->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'rejected', 'cancelled' => 'bg-red-100 text-red-800',
            'delivered', 'completed' => 'bg-green-100 text-green-800',
            default => 'bg-blue-100 text-blue-800',
        };

        $timeline = [
            'pending' => ['label' => 'Pending', 'icon' => 'fa-hourglass-half'],
            'confirmed_by_seller' => ['label' => 'Confirmed', 'icon' => 'fa-check-circle'],
            'preparing' => ['label' => 'Preparing', 'icon' => 'fa-kitchen-set'],
            'ready_for_pickup' => ['label' => 'Ready for pickup', 'icon' => 'fa-box'],
            'assigned_to_rider' => ['label' => 'Rider assigned', 'icon' => 'fa-motorcycle'],
            'picked_up' => ['label' => 'Picked up', 'icon' => 'fa-truck-fast'],
            'delivered' => ['label' => 'Delivered', 'icon' => 'fa-location-dot'],
            'completed' => ['label' => 'Completed', 'icon' => 'fa-flag-checkered'],
        ];

        $timelineKeys = array_keys($timeline);
        $currentIndex = array_search($order->status, $timelineKeys);
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold font-display text-gray-900">Track Order</h1>
                <p class="text-gray-500 mt-1">Order <span class="font-semibold text-gray-900">#{{ $order->id }}</span></p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusColor }}">
                {{ $prettyStatus }}
            </span>
        </div>

        {{-- ETA Countdown Card --}}
        @if($order->estimated_delivery_at && !in_array($order->status, ['rejected', 'cancelled']))
            <div class="bg-gradient-to-r from-primary to-primary-dark rounded-2xl shadow-sm border border-primary/20 overflow-hidden mb-8 text-white">
                <div class="p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            @if(in_array($order->status, ['delivered', 'completed']))
                                <i class="fas fa-check-circle text-2xl"></i>
                            @else
                                <i class="fas fa-clock text-2xl animate-pulse"></i>
                            @endif
                        </div>
                        <div>
                            @if(in_array($order->status, ['delivered', 'completed']))
                                <div class="text-sm font-medium opacity-90">Order</div>
                                <div class="text-2xl font-bold">Delivered! ✅</div>
                            @else
                                <div class="text-sm font-medium opacity-90">Estimated Delivery</div>
                                <div class="text-2xl font-bold" id="eta-countdown">Calculating...</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm opacity-80">Expected by</div>
                        <div class="text-lg font-semibold">{{ $order->estimated_delivery_at->format('h:i A') }}</div>
                        <div class="text-xs opacity-70">{{ $order->estimated_delivery_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            @if(!in_array($order->status, ['delivered', 'completed']))
                <script>
                    (function() {
                        const etaTime = new Date('{{ $order->estimated_delivery_at->toIso8601String() }}').getTime();
                        const countdownEl = document.getElementById('eta-countdown');

                        function updateCountdown() {
                            const now = new Date().getTime();
                            const diff = etaTime - now;

                            if (diff <= 0) {
                                countdownEl.textContent = 'Arriving any moment! 🚀';
                                return;
                            }

                            const hours = Math.floor(diff / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                            if (hours > 0) {
                                countdownEl.textContent = 'Arriving in ~' + hours + 'h ' + minutes + 'min';
                            } else {
                                countdownEl.textContent = 'Arriving in ~' + minutes + ' min 🏍️';
                            }
                        }

                        updateCountdown();
                        setInterval(updateCountdown, 30000); // Update every 30 seconds
                    })();
                </script>
            @endif
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Status timeline</h2>
                <p class="text-sm text-gray-500 mt-1">Updates reflect the latest status from seller/rider. Estimated delivery is 30-45 minutes after confirmation.</p>
            </div>
            <div class="p-6">
                @if(in_array($order->status, ['rejected', 'cancelled']))
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 flex items-start gap-3">
                        <i class="fas fa-triangle-exclamation mt-1"></i>
                        <div>
                            <div class="font-semibold">Order {{ $order->status }}</div>
                            <div class="text-sm opacity-90">If you need help, contact support.</div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($timeline as $key => $step)
                            @php
                                $stepIndex = array_search($key, $timelineKeys);
                                $isDone = $currentIndex !== false && $stepIndex !== false && $stepIndex <= $currentIndex;
                            @endphp
                            <div class="rounded-xl border {{ $isDone ? 'border-primary/20 bg-primary/5' : 'border-gray-200 bg-white' }} p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $isDone ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500' }}">
                                        <i class="fas {{ $step['icon'] }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-gray-900">{{ $step['label'] }}</div>
                                        <div class="text-xs {{ $isDone ? 'text-primary' : 'text-gray-500' }}">
                                            {{ $isDone ? 'Done' : 'Pending' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Order details</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Placed on</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ $order->created_at->format('M d, Y - h:i A') }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Total</div>
                            <div class="text-sm font-semibold text-primary mt-1">{{ number_format($order->total_amount) }} PKR</div>
                        </div>
                        @if($order->estimated_delivery_at)
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">ETA</div>
                                <div class="text-sm font-medium text-gray-900 mt-1">{{ $order->estimated_delivery_at->format('h:i A') }}</div>
                            </div>
                        @endif
                        @if($order->delivery_proof_image)
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Delivery proof</div>
                                <a href="{{ asset('storage/' . $order->delivery_proof_image) }}" target="_blank" class="text-sm font-semibold text-primary mt-1 inline-block">View photo</a>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Delivery address</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ $order->address }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <div class="p-6 flex items-start justify-between gap-6">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $item->item_name ?? $item->product?->name ?? $item->globalProduct?->name ?? 'Product' }}</div>
                                    <div class="text-sm text-gray-500 mt-1">{{ $item->quantity }} x {{ number_format($item->price) }} PKR</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900">{{ number_format($item->quantity * $item->price) }} PKR</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @if(in_array($order->status, ['confirmed_by_seller', 'preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'delivered', 'completed']))
                            <a href="{{ route('chat.index', $order->id) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-primary text-white font-semibold hover:bg-primary-dark transition-colors">
                                <i class="fas fa-comments mr-2"></i> Chat with Seller
                            </a>
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
