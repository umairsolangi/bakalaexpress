@extends('rider.layouts.app')

@section('title', 'Rider Dashboard - Bakala Express')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Manage your deliveries and earnings')

@section('content')
<div class="animate-stagger">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sidebar via-sidebar-light to-sidebar-lighter p-8 mb-8">
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-orange-500/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-white font-display mb-2">
                    Welcome, {{ $rider->name }} 🏍️
                </h2>
                <p class="text-stone-400 text-sm max-w-lg">
                    Track your deliveries, manage pickups, and earn more with Bakala Express.
                </p>
                <div class="flex items-center gap-3 mt-4">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full {{ $rider->status === 'online' ? 'bg-emerald-500/20 border border-emerald-500/30' : 'bg-red-500/20 border border-red-500/30' }}">
                        <span class="relative flex h-2 w-2">
                            @if($rider->status === 'online')
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-400"></span>
                            @endif
                        </span>
                        <span class="text-xs font-semibold {{ $rider->status === 'online' ? 'text-emerald-300' : 'text-red-300' }}">
                            {{ ucfirst($rider->status) }}
                        </span>
                    </div>
                    <form action="{{ route('rider.toggle-status') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $rider->status === 'online'
                                ? 'bg-red-500/20 text-red-300 border border-red-500/30 hover:bg-red-500/30'
                                : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30' }}">
                            <i class="fas {{ $rider->status === 'online' ? 'fa-power-off' : 'fa-check-circle' }} mr-1"></i>
                            {{ $rider->status === 'online' ? 'Go Offline' : 'Go Online' }}
                        </button>
                    </form>
                </div>
            </div>
            <div class="hidden md:block text-right">
                <div class="text-stone-400 text-xs font-medium uppercase tracking-wide mb-1">Today</div>
                <div class="text-white text-lg font-bold font-display">{{ now()->format('l') }}</div>
                <div class="text-stone-400 text-sm">{{ now()->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <!-- Active Deliveries -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-route text-blue-500 text-lg"></i>
                </div>
                @if($activeOrders->count() > 0)
                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold animate-pulse">Active</span>
                @endif
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Active Deliveries</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $activeOrders->count() }}</p>
        </div>

        <!-- Available Requests -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-list-ul text-amber-500 text-lg"></i>
                </div>
                @if($availableOrders->count() > 0)
                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">New</span>
                @endif
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Available Requests</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $availableOrders->count() }}</p>
        </div>

        <!-- Delivered Today -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-check-double text-emerald-500 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Delivered Today</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $todayDeliveredCount }}</p>
        </div>

        <!-- Current Earnings -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-wallet text-violet-500 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Trip Earnings</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ number_format($currentEarnings) }} <span class="text-sm text-gray-400 font-medium">PKR</span></p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Deliveries -->
        <div class="card-elevated overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <i class="fas fa-route text-blue-500"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Active Deliveries</h3>
                        <p class="text-xs text-gray-400">Orders assigned to you</p>
                    </div>
                </div>
                @if($activeOrders->count() > 0)
                    <span class="px-3 py-1 bg-blue-50 rounded-full text-xs font-semibold text-blue-600">{{ $activeOrders->count() }} active</span>
                @endif
            </div>
            <div class="p-5">
                @if($activeOrders->count() > 0)
                    <div class="space-y-4">
                        @foreach($activeOrders as $order)
                            <div class="border border-blue-100 rounded-2xl p-5 bg-blue-50/30 hover:bg-blue-50/60 transition-colors">
                                <!-- Order Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-[0.7rem] font-bold">Order #{{ $order->id }}</span>
                                        <p class="text-sm font-bold text-gray-900 mt-1.5">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                                    </div>
                                    <span class="text-lg font-extrabold text-gray-900 font-display">PKR {{ number_format($order->total_amount) }}</span>
                                </div>

                                <!-- Route Info -->
                                <div class="relative pl-6 mb-5">
                                    <!-- Vertical line -->
                                    <div class="absolute left-[11px] top-3 bottom-3 w-[2px] bg-gradient-to-b from-emerald-400 to-red-400 rounded-full"></div>

                                    <!-- Pickup -->
                                    <div class="flex items-start gap-3 mb-5 relative">
                                        <div class="absolute -left-6 w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 border-2 border-white shadow-sm">
                                            <i class="fas fa-store text-emerald-500 text-[0.5rem]"></i>
                                        </div>
                                        <div class="ml-1">
                                            <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide">Pickup</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ $order->seller->name ?? 'Store' }}</p>
                                            <p class="text-xs text-gray-400">{{ $order->seller->full_address ?? (($order->seller->city ?? '') . ' ' . ($order->seller->area ?? '')) }}</p>
                                        </div>
                                    </div>

                                    <!-- Delivery -->
                                    <div class="flex items-start gap-3 relative">
                                        <div class="absolute -left-6 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 border-2 border-white shadow-sm">
                                            <i class="fas fa-map-marker-alt text-red-500 text-[0.5rem]"></i>
                                        </div>
                                        <div class="ml-1">
                                            <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide">Deliver to</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ $order->user->name ?? 'Customer' }}</p>
                                            <p class="text-xs text-gray-400">{{ $order->address ?? 'Address not provided' }}</p>
                                            @if($order->phone || ($order->user && $order->user->mobile))
                                                <a href="tel:{{ $order->phone ?? $order->user->mobile }}" class="text-xs text-emerald-600 font-semibold hover:underline inline-flex items-center gap-1 mt-1">
                                                    <i class="fas fa-phone-alt text-[0.65rem]"></i> {{ $order->phone ?? $order->user->mobile }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($order->delivery_instructions))
                                    <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
                                        <i class="fas fa-sticky-note text-amber-500 mt-0.5 flex-shrink-0"></i>
                                        <div><span class="font-bold">Customer Instruction:</span> {{ $order->delivery_instructions }}</div>
                                    </div>
                                @endif

                                <!-- Products Checklist / Details for Rider -->
                                <div class="mb-5 bg-white rounded-xl border border-blue-100 p-3.5 shadow-sm">
                                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                                <i class="fas fa-shopping-basket"></i>
                                            </span>
                                            <span class="text-xs font-bold text-gray-900 uppercase tracking-wide">
                                                Products to Collect ({{ $order->items->count() }})
                                            </span>
                                        </div>
                                        <span class="text-[11px] font-semibold text-gray-500">
                                            Total: {{ $order->items->sum('quantity') }} items
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse($order->items as $item)
                                            @php
                                                $itemName = $item->item_name ?? $item->product?->name ?? $item->globalProduct?->name ?? 'Grocery Item';
                                                $itemImg = $item->item_image ?? $item->product?->image ?? $item->globalProduct?->image_url ?? null;
                                            @endphp
                                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-100 hover:bg-gray-100/70 transition-colors">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    @if($itemImg)
                                                        <img src="{{ $itemImg }}" alt="{{ $itemName }}" class="w-10 h-10 rounded-md object-cover border border-gray-200 flex-shrink-0">
                                                    @else
                                                        <div class="w-10 h-10 rounded-md bg-gray-200 flex items-center justify-center text-gray-400 flex-shrink-0 text-xs">
                                                            <i class="fas fa-box"></i>
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold text-gray-900 truncate">{{ $itemName }}</p>
                                                        <p class="text-[11px] text-gray-500">
                                                            <span class="font-semibold text-emerald-600">PKR {{ number_format($item->price) }}</span>
                                                            @if($item->unit_type)
                                                                <span class="text-gray-400">/ {{ $item->unit_type }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="text-right flex-shrink-0 ml-2">
                                                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-900 text-xs font-black">
                                                        Qty: {{ $item->quantity }}
                                                    </span>
                                                    <p class="text-[11px] font-bold text-gray-700 mt-0.5">
                                                        PKR {{ number_format($item->price * $item->quantity) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-400 py-2 text-center">No product details found for this order.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <form action="{{ route('rider.order.update-status', $order->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @if($order->status == 'assigned_to_rider')
                                        <button type="submit" name="status" value="picked_up"
                                            class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 transition-all shadow-md shadow-amber-500/20 flex items-center justify-center gap-2">
                                            <i class="fas fa-box-open"></i> Confirm Pickup
                                        </button>
                                    @elseif($order->status == 'picked_up')
                                        <div class="mb-3">
                                            <label class="block text-[0.65rem] text-gray-500 uppercase font-semibold tracking-wide mb-1.5">Delivery Proof Photo</label>
                                            <input type="file" name="delivery_proof_image" accept="image/*"
                                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-600 hover:file:bg-emerald-100 cursor-pointer">
                                        </div>
                                        <button type="submit" name="status" value="delivered"
                                            class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 transition-all shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2">
                                            <i class="fas fa-check-circle"></i> Confirm Delivery
                                        </button>
                                    @endif
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-14">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clipboard-check text-gray-300 text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-1">No Active Deliveries</h3>
                        <p class="text-xs text-gray-400">Accept an order to get started</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Available Orders -->
        <div class="card-elevated overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                        <i class="fas fa-list-ul text-amber-500"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Available Orders</h3>
                        <p class="text-xs text-gray-400">Ready for pickup nearby</p>
                    </div>
                </div>
                @if($availableOrders->count() > 0 && $rider->status === 'online')
                    <span class="px-3 py-1 bg-amber-50 rounded-full text-xs font-semibold text-amber-600">{{ $availableOrders->count() }} new</span>
                @endif
            </div>
            <div class="p-5">
                @if($rider->status === 'online')
                    @if($availableOrders->count() > 0)
                        <div class="space-y-4">
                            @foreach($availableOrders as $order)
                                <div class="border border-gray-100 rounded-2xl p-5 hover:border-amber-200 hover:shadow-sm transition-all group/card">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 text-[0.7rem] font-bold group-hover/card:bg-amber-100 group-hover/card:text-amber-700 transition-colors">New Request</span>
                                            <p class="text-sm font-bold text-gray-900 mt-1.5">Order #{{ $order->id }}</p>
                                        </div>
                                        <span class="text-lg font-extrabold text-gray-900 font-display group-hover/card:text-amber-600 transition-colors">PKR {{ number_format($order->total_amount) }}</span>
                                    </div>

                                    <div class="space-y-2.5 mb-4">
                                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50">
                                            <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center shadow-sm flex-shrink-0">
                                                <i class="fas fa-store text-emerald-500 text-xs"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[0.6rem] text-gray-400 font-semibold uppercase tracking-wide">From</p>
                                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $order->seller->name ?? 'Bakala Seller' }}</p>
                                                <p class="text-xs text-gray-400 truncate">{{ $order->seller->city ?? '' }}, {{ $order->seller->area ?? '' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50">
                                            <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center shadow-sm flex-shrink-0">
                                                <i class="fas fa-map-marker-alt text-red-500 text-xs"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[0.6rem] text-gray-400 font-semibold uppercase tracking-wide">To</p>
                                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $order->address ?? 'Address pending' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Items Preview -->
                                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 mb-4">
                                        <div class="flex items-center justify-between text-xs mb-2 font-bold text-gray-800">
                                            <span class="flex items-center gap-1.5 text-gray-700">
                                                <i class="fas fa-shopping-bag text-amber-500"></i> Items to Collect:
                                            </span>
                                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-[11px]">
                                                {{ $order->items->count() }} items ({{ $order->items->sum('quantity') }} pcs)
                                            </span>
                                        </div>
                                        <div class="space-y-1.5">
                                            @foreach($order->items->take(4) as $item)
                                                @php
                                                    $itemName = $item->item_name ?? $item->product?->name ?? $item->globalProduct?->name ?? 'Product Item';
                                                @endphp
                                                <div class="flex items-center justify-between text-xs text-gray-600">
                                                    <span class="truncate max-w-[200px] sm:max-w-[240px] font-medium">• {{ $itemName }}</span>
                                                    <span class="font-extrabold text-gray-800 flex-shrink-0">x{{ $item->quantity }}</span>
                                                </div>
                                            @endforeach
                                            @if($order->items->count() > 4)
                                                <p class="text-[11px] text-gray-400 italic pt-0.5">+ {{ $order->items->count() - 4 }} more item(s)</p>
                                            @endif
                                        </div>
                                    </div>

                                    <form action="{{ route('rider.order.accept', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full py-3 px-4 rounded-xl text-sm font-bold border-2 border-amber-400 text-amber-600 bg-white hover:bg-gradient-to-r hover:from-amber-500 hover:to-orange-500 hover:text-white hover:border-transparent transition-all flex items-center justify-center gap-2">
                                            Accept Order <i class="fas fa-arrow-right text-xs group-hover/card:translate-x-1 transition-transform"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-14">
                            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-inbox text-gray-300 text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 mb-1">No Orders Available</h3>
                            <p class="text-xs text-gray-400">Wait for new requests to appear</p>
                        </div>
                    @endif
                @else
                    <!-- Offline State -->
                    <div class="text-center py-14">
                        <div class="w-20 h-20 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-power-off text-red-400 text-3xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">You are Offline</h3>
                        <p class="text-sm text-gray-400 max-w-xs mx-auto mb-5">Go online to start receiving delivery requests from nearby sellers.</p>
                        <form action="{{ route('rider.toggle-status') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn-rider">
                                <i class="fas fa-check-circle"></i> Go Online
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
