@extends('seller.layouts.app')

@section('page-title', 'Order #' . $order->id)
@section('page-subtitle', 'Manage order details and status')

@section('styles')
<style>
    .status-stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        padding: 0 8px;
    }

    .status-stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 40px;
        right: 40px;
        height: 3px;
        background: #e5e7eb;
        border-radius: 99px;
        z-index: 0;
    }

    .stepper-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
        max-width: 100px;
    }

    .stepper-dot {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .stepper-dot.completed {
        background: linear-gradient(135deg, #00A651, #008c44);
        color: #fff;
        box-shadow: 0 4px 12px rgba(0,166,81,0.3);
    }

    .stepper-dot.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
        box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        animation: pulse 2s infinite;
    }

    .stepper-dot.upcoming {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .stepper-dot.rejected {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        box-shadow: 0 4px 12px rgba(239,68,68,0.3);
    }

    .stepper-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-align: center;
        color: #94a3b8;
        line-height: 1.3;
    }

    .stepper-label.completed { color: #059669; }
    .stepper-label.active { color: #2563eb; }
    .stepper-label.rejected { color: #dc2626; }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 4px 12px rgba(59,130,246,0.3); }
        50% { box-shadow: 0 4px 20px rgba(59,130,246,0.5); }
    }
</style>
@endsection

@section('content')
@php
    $allStatuses = ['pending', 'confirmed_by_seller', 'preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'delivered', 'completed'];
    $currentIndex = array_search($order->status, $allStatuses);
    $isRejected = $order->status === 'rejected';

    $statusFlow = [
        'confirmed_by_seller' => 'preparing',
        'preparing' => 'ready_for_pickup',
        'ready_for_pickup' => 'assigned_to_rider',
        'assigned_to_rider' => 'picked_up',
        'picked_up' => 'delivered',
        'delivered' => 'completed',
    ];
    $nextStatus = $statusFlow[$order->status] ?? null;

    $statusIcons = [
        'pending' => 'fas fa-clock',
        'confirmed_by_seller' => 'fas fa-check',
        'preparing' => 'fas fa-utensils',
        'ready_for_pickup' => 'fas fa-box',
        'assigned_to_rider' => 'fas fa-motorcycle',
        'picked_up' => 'fas fa-truck',
        'delivered' => 'fas fa-home',
        'completed' => 'fas fa-flag-checkered',
    ];
@endphp

<div class="animate-stagger">
    <!-- Order Header Card -->
    <div class="card-elevated overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-sidebar to-sidebar-light p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-xl font-bold text-white font-display">Order #{{ $order->id }}</h2>
                        @if($isRejected)
                            <span class="px-3 py-1 rounded-lg bg-red-500/20 text-red-300 text-xs font-bold border border-red-500/30">Rejected</span>
                        @else
                            <span class="px-3 py-1 rounded-lg bg-primary/20 text-emerald-300 text-xs font-bold border border-primary/30">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        @endif
                    </div>
                    <p class="text-slate-400 text-sm">{{ $order->created_at->format('F d, Y - h:i A') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Amount</p>
                    <p class="text-2xl font-extrabold text-white font-display">{{ number_format($order->total_amount) }} <span class="text-sm text-slate-400">PKR</span></p>
                </div>
            </div>
        </div>

        <!-- Status Stepper -->
        @if(!$isRejected)
        <div class="p-6 overflow-x-auto">
            <div class="status-stepper min-w-[600px]">
                @foreach($allStatuses as $index => $status)
                    @php
                        $dotClass = 'upcoming';
                        $labelClass = '';
                        if ($index < $currentIndex) { $dotClass = 'completed'; $labelClass = 'completed'; }
                        elseif ($index == $currentIndex) { $dotClass = 'active'; $labelClass = 'active'; }
                    @endphp
                    <div class="stepper-step">
                        <div class="stepper-dot {{ $dotClass }}">
                            @if($index < $currentIndex)
                                <i class="fas fa-check"></i>
                            @else
                                <i class="{{ $statusIcons[$status] ?? 'fas fa-circle' }} text-[0.7rem]"></i>
                            @endif
                        </div>
                        <span class="stepper-label {{ $labelClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="card-elevated overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-violet-500"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-5 py-3 text-left text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Service</th>
                                <th class="px-5 py-3 text-center text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Qty</th>
                                <th class="px-5 py-3 text-right text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Price</th>
                                <th class="px-5 py-3 text-right text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($order->items as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $item->product->name ?? 'Product Unavailable' }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 text-center">{{ $item->quantity }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 text-right">{{ number_format($item->price) }} PKR</td>
                                    <td class="px-5 py-4 text-sm font-bold text-gray-900 text-right">{{ number_format($item->quantity * $item->price) }} PKR</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50/80">
                                <td colspan="3" class="px-5 py-3 text-sm font-bold text-gray-900 text-right">Total</td>
                                <td class="px-5 py-3 text-sm font-extrabold text-primary text-right">{{ number_format($order->total_amount) }} PKR</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="card-elevated overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center">
                        <i class="fas fa-user text-sky-500"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Customer Details</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                <i class="fas fa-user text-gray-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide">Name</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $order->user->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                <i class="fas fa-envelope text-gray-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide">Email</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $order->user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                <i class="fas fa-phone text-gray-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide">Mobile</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $order->user->mobile ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                <i class="fas fa-map-marker-alt text-gray-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[0.65rem] text-gray-400 font-semibold uppercase tracking-wide">Address</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $order->user->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column (1/3) -->
        <div class="space-y-6">
            <!-- Payment Details -->
            <div class="card-elevated overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <i class="fas fa-credit-card text-emerald-500"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Payment</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Mode</span>
                        <span class="text-sm font-bold text-gray-900">{{ $order->transaction_id ? 'Online' : 'Cash on Delivery' }}</span>
                    </div>
                    @if($order->transaction_id)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Transaction ID</span>
                            <span class="text-sm font-medium text-gray-700 font-mono">{{ $order->transaction_id }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Seller Info -->
            <div class="card-elevated overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                        <i class="fas fa-store text-amber-500"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Seller Info</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Name</span>
                        <span class="text-sm font-bold text-gray-900">{{ $order->seller->name }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Email</span>
                        <span class="text-sm font-medium text-gray-700">{{ $order->seller->email }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Contact</span>
                        <span class="text-sm font-medium text-gray-700">{{ $order->seller->contacts ?? $order->seller->city }}</span>
                    </div>
                </div>
            </div>

            <!-- Update Status -->
            <div class="card-elevated overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-indigo-500"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Update Status</h3>
                </div>
                <div class="p-5">
                    <form action="{{ route('order.updateStatus', $order) }}" method="POST">
                        @csrf

                        @if($order->status === 'rejected' || !$nextStatus)
                            <div class="p-4 rounded-xl bg-gray-50 text-center mb-4">
                                <i class="fas {{ $order->status === 'rejected' ? 'fa-ban text-red-400' : 'fa-check-double text-emerald-400' }} text-2xl mb-2"></i>
                                <p class="text-sm font-semibold text-gray-600">
                                    {{ $order->status === 'rejected' ? 'Order Rejected' : 'Order Completed' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">No further actions available</p>
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Next Status</label>
                                <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required>
                                    <option value="{{ $nextStatus }}">
                                        {{ ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                    </option>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary-custom w-full justify-center">
                                <i class="fas fa-save"></i> Update Status
                            </button>
                        @endif
                    </form>

                    <div class="flex flex-col gap-2 mt-4">
                        @if(in_array($order->status, ['confirmed_by_seller', 'preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'delivered', 'completed']))
                            <a href="{{ route('seller.chat.index', $order->id) }}"
                                class="btn-secondary-custom w-full justify-center text-sm">
                                <i class="fas fa-comments text-blue-500"></i> Chat with Customer
                            </a>
                        @endif
                        <a href="{{ route('seller.panel') }}" class="btn-secondary-custom w-full justify-center text-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection