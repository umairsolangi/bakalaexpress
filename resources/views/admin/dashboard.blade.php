@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('styles')
    <style>
        .premium-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .premium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.08);
        }
        .chart-glow {
            filter: drop-shadow(0px 8px 16px rgba(0, 166, 81, 0.08));
        }
        @keyframes countUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .stat-animate { animation: countUp 0.5s ease-out forwards; }
        .stat-animate:nth-child(2) { animation-delay: 0.1s; }
        .stat-animate:nth-child(3) { animation-delay: 0.2s; }
        .stat-animate:nth-child(4) { animation-delay: 0.3s; }
    </style>
@endsection

@section('content')
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8 gap-4">
        <div>
            <p class="text-sm text-gray-400 font-medium mb-1">Welcome back,</p>
            <h1 class="text-3xl font-extrabold text-gray-950 font-display tracking-tight">
                Dashboard Overview
            </h1>
            <p class="text-gray-400 mt-1.5 text-sm">
                Real-time snapshot of business operations, metrics, and pending approvals.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-3 py-1.5 rounded-lg">
                <i class="fas fa-calendar-alt mr-1.5"></i>{{ now()->format('l, M d, Y') }}
            </span>
            <a href="{{ route('admin.moderation.queue') }}"
                class="inline-flex items-center px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow-md hover:shadow-lg gap-2 transform active:scale-95">
                <i class="fas fa-clipboard-check"></i> Moderation Queue
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Revenue -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 premium-card relative overflow-hidden stat-animate">
            <div class="stat-stripe bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
            <div class="pl-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-emerald-600"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-full">Revenue</span>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Revenue</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-0.5 font-display">{{ number_format($totalRevenue, 2) }} <span class="text-sm font-bold text-gray-400">PKR</span></p>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 premium-card relative overflow-hidden stat-animate">
            <div class="stat-stripe bg-gradient-to-b from-blue-400 to-blue-600"></div>
            <div class="pl-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-blue-600"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full">Sales</span>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Orders</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-0.5 font-display">{{ $totalOrders }}</p>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 premium-card relative overflow-hidden stat-animate">
            <div class="stat-stripe bg-gradient-to-b from-violet-400 to-violet-600"></div>
            <div class="pl-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-violet-600"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 bg-violet-50 text-violet-600 rounded-full">Users</span>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Customers</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-0.5 font-display">{{ $totalUsers }}</p>
            </div>
        </div>

        <!-- Active Sellers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 premium-card relative overflow-hidden stat-animate">
            <div class="stat-stripe bg-gradient-to-b from-amber-400 to-amber-600"></div>
            <div class="pl-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-store text-amber-600"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 bg-amber-50 text-amber-600 rounded-full">Sellers</span>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active Sellers</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-0.5 font-display">{{ count($sellers) }}</p>
            </div>
        </div>
    </div>

    <!-- Chart & Verification Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 lg:col-span-2 flex flex-col">
            <div class="flex items-center justify-between p-6 pb-0">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Orders Trend</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Volume of orders placed in the last 7 days</p>
                </div>
                <div class="text-[10px] font-bold px-3 py-1 bg-gray-50 text-gray-500 rounded-full border border-gray-100">
                    Weekly Activity
                </div>
            </div>
            <div class="flex-1 p-6 pt-4 chart-glow relative min-h-[260px]">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        <!-- Verification Alerts -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Verification Alerts</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Sellers requesting identity status</p>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 rounded-full border border-amber-100">{{ count($pendingSellers) }}</span>
            </div>
            <div class="flex-1 overflow-y-auto max-h-[300px] divide-y divide-gray-50">
                @forelse($pendingSellers->take(5) as $seller)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                {{ strtoupper(substr($seller->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">{{ $seller->name }}</h4>
                                <p class="text-[11px] text-gray-400">{{ $seller->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.verifications.index') }}" class="p-1.5 bg-primary/8 hover:bg-primary/15 text-primary text-[10px] font-bold rounded-lg transition-colors">
                            Audit
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 flex flex-col items-center justify-center h-full">
                        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-primary mb-3">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <p class="text-sm font-medium">All sellers verified</p>
                    </div>
                @endforelse
            </div>
            @if(count($pendingSellers) > 5)
                <div class="p-3 bg-gray-50/50 border-t text-center">
                    <a href="{{ route('admin.verifications.index') }}" class="text-xs font-bold text-primary hover:text-primary-dark">
                        View All {{ count($pendingSellers) }} Requests →
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Product Approvals & New Users -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Pending Products -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2 flex flex-col">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Product Approvals</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Listings pending administrative approval</p>
                </div>
                <a href="{{ route('admin.products') }}" class="text-xs font-semibold text-primary hover:text-primary-dark">
                    Manage Products <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="flex-1 overflow-x-auto">
                @if(count($pendingProducts) > 0)
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seller</th>
                                <th scope="col" class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @foreach($pendingProducts->take(5) as $product)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->seller ? $product->seller->name : 'Unknown Seller' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm font-semibold text-gray-950">
                                        {{ number_format($product->price, 2) }} PKR
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-1.5">
                                            <form action="{{ route('admin.approveProduct', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-7 h-7 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition-colors" title="Approve">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.rejectProduct', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Reject">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-10 text-center text-gray-400 flex flex-col items-center justify-center h-full">
                        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-primary mb-3">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <p class="text-sm font-medium">All products approved</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Customers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-gray-900">New Users</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Latest registered customers</p>
                </div>
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            </div>
            <div class="flex-1 divide-y divide-gray-50">
                @forelse($recentUsers as $user)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">{{ $user->name }}</h4>
                                <p class="text-[11px] text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 flex flex-col items-center justify-center h-full">
                        <p class="text-sm font-medium">No registered customers yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-gray-900">Recent Orders</h3>
                <p class="text-xs text-gray-400 mt-0.5">Overview of recent transactions across the platform</p>
            </div>
            <span class="text-[10px] font-bold px-2.5 py-1 bg-gray-50 text-gray-500 rounded-full border border-gray-100">Updated Live</span>
        </div>
        <div class="overflow-x-auto">
            @if(count($recentOrders) > 0)
                <table class="min-w-full divide-y divide-gray-50">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order ID</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap text-sm font-bold text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-950">{{ $order->user ? $order->user->name : 'Guest User' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $order->user ? $order->user->email : '' }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ number_format($order->total_amount, 2) }} PKR
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @php
                                        $status = strtolower($order->status);
                                        $badgeClass = 'bg-gray-100 text-gray-700';
                                        if (in_array($status, ['completed', 'delivered', 'approved'])) {
                                            $badgeClass = 'bg-green-50 text-green-700';
                                        } elseif (in_array($status, ['pending', 'processing', 'ordered'])) {
                                            $badgeClass = 'bg-yellow-50 text-yellow-700';
                                        } elseif (in_array($status, ['cancelled', 'rejected', 'failed'])) {
                                            $badgeClass = 'bg-red-50 text-red-700';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('M d, Y h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-gray-400">
                    <i class="fas fa-shopping-cart text-3xl mb-3 text-gray-300"></i>
                    <p class="text-sm">No orders recorded in the system.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Registered Sellers Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-gray-900">Registered Sellers</h3>
                <p class="text-xs text-gray-400 mt-0.5">Directory of approved sellers on the platform</p>
            </div>
            <a href="{{ route('admin.sellers') }}" class="text-xs font-semibold text-primary hover:text-primary-dark">
                View All Sellers <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Business</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Joined</th>
                        <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($sellers->take(5) as $seller)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-500">#{{ $seller->id }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm mr-3">
                                        {{ substr($seller->name, 0, 1) }}
                                    </div>
                                    <div class="text-sm font-semibold text-gray-950">{{ $seller->name }}</div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-500">{{ $seller->email }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-500">{{ $seller->business_name ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-500">{{ $seller->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('admin.loginSeller', $seller->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors text-xs font-semibold">
                                        Login as Seller
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                <p class="text-sm">No registered sellers found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ordersChart').getContext('2d');

            // Refined gradient for line fill
            const gradient = ctx.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(0, 166, 81, 0.15)');
            gradient.addColorStop(1, 'rgba(0, 166, 81, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($orderChartLabels) !!},
                    datasets: [{
                        label: 'Orders',
                        data: {!! json_encode($orderChartData) !!},
                        borderColor: '#00A651',
                        borderWidth: 2.5,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#00A651',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#008c44',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Poppins', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Inter', size: 12 },
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(243, 244, 246, 1)',
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0,
                                font: { family: 'Inter', size: 11, color: '#9CA3AF' }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { family: 'Inter', size: 11, color: '#9CA3AF' }
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true
                }
            });
        });
    </script>
@endsection