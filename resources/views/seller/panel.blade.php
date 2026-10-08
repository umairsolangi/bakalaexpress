@extends('seller.layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Here\'s what\'s happening with your store today')

@section('content')
<div class="animate-stagger">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sidebar via-sidebar-light to-sidebar-lighter p-8 mb-8">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-500/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-white font-display mb-2">
                    Welcome back, {{ auth()->guard('seller')->user()->name }} 👋
                </h2>
                <p class="text-slate-300 text-sm max-w-lg">
                    Manage your products, track orders, and grow your business on Bakala Express.
                </p>
                <div class="flex items-center gap-3 mt-4">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full {{ auth()->guard('seller')->user()->is_open ? 'bg-emerald-500/20 border border-emerald-500/30' : 'bg-red-500/20 border border-red-500/30' }}">
                        <span class="relative flex h-2 w-2">
                            @if(auth()->guard('seller')->user()->is_open)
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-400"></span>
                            @endif
                        </span>
                        <span class="text-xs font-semibold {{ auth()->guard('seller')->user()->is_open ? 'text-emerald-300' : 'text-red-300' }}">
                            {{ auth()->guard('seller')->user()->is_open ? 'Store Open' : 'Store Closed' }}
                        </span>
                    </div>
                    @if(auth()->guard('seller')->user()->is_verified)
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-500/20 border border-blue-500/30">
                            <i class="fas fa-check-circle text-blue-300 text-xs"></i>
                            <span class="text-xs font-semibold text-blue-300">Verified</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="hidden md:block">
                <div class="text-right">
                    <div class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-1">Today</div>
                    <div class="text-white text-lg font-bold font-display">{{ now()->format('l') }}</div>
                    <div class="text-slate-400 text-sm">{{ now()->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operating Hours -->
    <div class="card-elevated p-6 mb-8">
        <form action="{{ route('seller.operating-hours.update') }}" method="POST">
            @csrf
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-amber-500"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Operating Hours</h3>
                        <p class="text-xs text-gray-400">Set when your store accepts orders</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4 flex-1">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Open</label>
                        <input type="time" name="opens_at" value="{{ substr((string) auth()->guard('seller')->user()->opens_at, 0, 5) }}"
                            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Close</label>
                        <input type="time" name="closes_at" value="{{ substr((string) auth()->guard('seller')->user()->closes_at, 0, 5) }}"
                            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="is_open" value="1" {{ auth()->guard('seller')->user()->is_open ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:bg-primary transition-colors"></div>
                            <div class="absolute left-[3px] top-[3px] bg-white w-[18px] h-[18px] rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Accepting orders</span>
                    </label>
                    <button type="submit" class="btn-primary-custom ml-auto">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <!-- Total Products -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-box text-blue-500 text-lg"></i>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-arrow-up text-blue-400 text-xs"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Products</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $totalProductsCount }}</p>
        </div>

        <!-- Total Orders -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-shopping-bag text-emerald-500 text-lg"></i>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i class="fas fa-arrow-up text-emerald-400 text-xs"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Orders</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $totalOrdersCount }}</p>
        </div>

        <!-- Pending Orders -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-hourglass-half text-amber-500 text-lg"></i>
                </div>
                @if($pendingOrdersCount > 0)
                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold animate-pulse-dot">Action</span>
                @endif
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pending Orders</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $pendingOrdersCount }}</p>
        </div>

        <!-- In Progress -->
        <div class="card-elevated p-6 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-truck text-indigo-500 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">In Progress</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1 font-display">{{ $processingOrdersCount }}</p>
        </div>
    </div>

    <!-- Quick Action Orders -->
    <div class="card-elevated overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center">
                    <i class="fas fa-bolt text-orange-500"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Quick Actions</h3>
                    <p class="text-xs text-gray-400">Orders that need your attention</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-semibold text-gray-500">Top 5</span>
        </div>
        <div class="p-5">
            @if ($quickActionOrders->isEmpty())
                <div class="text-center py-10">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-double text-gray-300 text-2xl"></i>
                    </div>
                    <p class="text-gray-400 text-sm font-medium">No immediate actions pending. Great work! 🎉</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($quickActionOrders as $order)
                        <div class="border border-gray-100 rounded-2xl p-5 hover:border-gray-200 hover:shadow-sm transition-all group/card">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Order #{{ $order->id }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->user->name ?? 'Customer' }}</p>
                                </div>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'confirmed_by_seller' => 'bg-blue-100 text-blue-700',
                                        'processing' => 'bg-blue-100 text-blue-700',
                                        'ready_for_pickup' => 'bg-indigo-100 text-indigo-700',
                                    ];
                                    $statusClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="px-2.5 py-1 text-[0.7rem] font-bold rounded-lg {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-900">PKR {{ number_format($order->total_amount) }}</span>
                                <div class="flex gap-2">
                                    @if($order->status === 'pending')
                                        <form action="{{ route('order.acceptReject', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="confirmed_by_seller"
                                                class="text-xs px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 font-semibold transition-colors">
                                                <i class="fas fa-check mr-1"></i> Accept
                                            </button>
                                        </form>
                                        <form action="{{ route('order.acceptReject', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="rejected"
                                                class="text-xs px-4 py-2 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 font-semibold transition-colors">
                                                <i class="fas fa-times mr-1"></i> Reject
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('seller.order.handle', $order) }}"
                                            class="text-xs px-4 py-2 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 font-semibold transition-colors">
                                            <i class="fas fa-arrow-right mr-1"></i> Manage
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Products & Orders Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Products Section -->
        <div class="card-elevated overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                        <i class="fas fa-store text-violet-500"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Your Products</h3>
                </div>
                <a href="{{ route('add.service') }}"
                    class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-primary/5">
                    Add New <i class="fas fa-plus text-[0.6rem]"></i>
                </a>
            </div>

            <div>
                @if ($products->isEmpty())
                    <div class="text-center py-14">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-box-open text-gray-300 text-2xl"></i>
                        </div>
                        <p class="text-gray-400 text-sm font-medium mb-4">You haven't added any products yet.</p>
                        <a href="{{ route('add.service') }}" class="btn-primary-custom text-sm">
                            <i class="fas fa-plus-circle"></i> Add Product
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach ($products->take(5) as $product)
                            <div class="flex items-center p-4 hover:bg-gray-50/50 transition-colors group/item">
                                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100 border border-gray-100">
                                    <img class="h-full w-full object-cover" src="{{ Storage::url($product->image) }}"
                                        alt="{{ $product->name }}">
                                </div>
                                <div class="ml-4 flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h4>
                                    <p class="text-xs text-gray-400">{{ $product->stock_quantity }} {{ $product->unit_type }} in stock</p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm font-bold text-gray-900">{{ $product->price }} <span class="text-xs text-gray-400">PKR</span></p>
                                    <div class="flex items-center justify-end gap-3 mt-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                        <a href="{{ route('seller.editService', $product->id) }}"
                                            class="text-xs text-blue-500 hover:text-blue-700 font-medium">Edit</a>
                                        <form action="{{ route('seller.deleteService', $product->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($products->count() > 5)
                        <div class="p-4 border-t border-gray-50 text-center">
                            <a href="{{ route('seller.catalog.index') }}" class="text-xs font-semibold text-gray-400 hover:text-primary transition-colors">
                                View All Products <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="card-elevated overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-sky-500"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Recent Orders</h3>
            </div>

            <div>
                @if ($orders->isEmpty())
                    <div class="text-center py-14">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clipboard-list text-gray-300 text-2xl"></i>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No orders received yet.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach ($orders->take(5) as $order)
                            <div class="flex items-center p-4 hover:bg-gray-50/50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-primary">#{{ $order->id }}</span>
                                        @php
                                            $orderStatusColors = [
                                                'completed' => 'bg-emerald-100 text-emerald-700',
                                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'cancelled' => 'bg-red-100 text-red-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                'confirmed_by_seller' => 'bg-blue-100 text-blue-700',
                                                'processing' => 'bg-blue-100 text-blue-700',
                                            ];
                                            $oStatusClass = $orderStatusColors[$order->status] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="px-2 py-0.5 text-[0.65rem] font-bold rounded-md {{ $oStatusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm font-bold text-gray-900">{{ $order->total_amount }} <span class="text-xs text-gray-400">PKR</span></p>
                                    <div class="mt-1">
                                        @if($order->status === 'pending')
                                            <div class="flex justify-end gap-1.5">
                                                <form action="{{ route('order.acceptReject', $order) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" name="status" value="confirmed_by_seller"
                                                        class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-500 hover:bg-emerald-100 flex items-center justify-center transition-colors" title="Accept">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('order.acceptReject', $order) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" name="status" value="rejected"
                                                        class="w-7 h-7 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 flex items-center justify-center transition-colors" title="Reject">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <a href="{{ route('seller.order.handle', $order) }}"
                                                class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors">
                                                Details <i class="fas fa-chevron-right text-[0.5rem] ml-0.5"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
