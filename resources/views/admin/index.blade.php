@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/adminDashboard.css') }}">
@endsection

@section('content')
<div class="page-enter">
    <h1 class="text-2xl font-extrabold text-gray-950 font-display tracking-tight mb-6">Admin Dashboard</h1>

    <!-- Alerts -->
    @if(session('success'))
    <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 flex items-center gap-3 text-green-800 toast-enter">
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check-circle text-green-600"></i>
        </div>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 card-hover relative overflow-hidden">
            <div class="stat-stripe bg-gradient-to-b from-blue-400 to-blue-600"></div>
            <div class="pl-3 flex items-center gap-4">
                <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 font-display">{{ $totalUsers }}</h3>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Users</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 card-hover relative overflow-hidden">
            <div class="stat-stripe bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
            <div class="pl-3 flex items-center gap-4">
                <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-store text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 font-display">{{ $totalSellers }}</h3>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Sellers</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 card-hover relative overflow-hidden">
            <div class="stat-stripe bg-gradient-to-b from-cyan-400 to-cyan-600"></div>
            <div class="pl-3 flex items-center gap-4">
                <div class="w-11 h-11 bg-cyan-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shopping-cart text-cyan-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 font-display">{{ $totalOrders }}</h3>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Orders</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 card-hover relative overflow-hidden">
            <div class="stat-stripe bg-gradient-to-b from-amber-400 to-amber-600"></div>
            <div class="pl-3 flex items-center gap-4">
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-money-bill-wave text-amber-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 font-display">{{ number_format($totalRevenue, 2) }} €</h3>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Orders Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <div class="flex items-center justify-between p-5 border-b border-gray-50">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-chart-line text-primary"></i> Orders Overview
                </h2>
                <div class="dropdown">
                    <button class="text-[10px] font-bold px-3 py-1 bg-gray-50 text-gray-500 rounded-full border border-gray-100" type="button" id="orderChartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Last 7 Days
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="orderChartDropdown">
                        <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                        <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex-1 p-5 min-h-[260px]">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        <!-- Verification Requests -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-5 border-b border-gray-50">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-user-check text-primary"></i> Verifications
                </h2>
                <a href="{{ route('admin.verifications.index') }}" class="text-xs font-bold text-primary hover:text-primary-dark">
                    View All
                </a>
            </div>
            <div class="flex-1 divide-y divide-gray-50">
                @if(count($pendingVerifications) > 0)
                    @foreach($pendingVerifications as $verification)
                    <a href="{{ route('admin.verifications.show', $verification->id) }}" class="block p-4 hover:bg-gray-50/50 transition-colors">
                        <div class="flex w-100 justify-between items-center">
                            <div>
                                <h6 class="text-sm font-semibold text-gray-900">{{ $verification->business_name }}</h6>
                                <p class="text-[11px] text-gray-400">{{ $verification->seller->name }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 rounded-full border border-amber-100">Pending</span>
                        </div>
                        <small class="text-[11px] text-gray-400 mt-1 block">Submitted {{ $verification->created_at->diffForHumans() }}</small>
                    </a>
                    @endforeach
                @else
                <div class="p-8 text-center flex flex-col items-center justify-center h-full">
                    <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-primary mb-3">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">No pending verification requests</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Orders & New Users -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-50">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-shopping-bag text-primary"></i> Recent Orders
                </h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-primary hover:text-primary-dark">
                    View All
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-50">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order ID</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @foreach($recentOrders as $order)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-bold text-gray-900">#{{ $order->id }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-700">{{ $order->user->name }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                    {{ strtolower($order->status) == 'completed' || strtolower($order->status) == 'delivered' ? 'bg-green-50 text-green-700' :
                                       (strtolower($order->status) == 'pending' || strtolower($order->status) == 'processing' ? 'bg-yellow-50 text-yellow-700' :
                                       (strtolower($order->status) == 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ number_format($order->total_amount, 2) }} €</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 inline-flex items-center justify-center transition-colors">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-5 border-b border-gray-50">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-user-plus text-primary"></i> New Users
                </h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-primary hover:text-primary-dark">
                    View All
                </a>
            </div>
            <div class="flex-1 divide-y divide-gray-50">
                @foreach($recentUsers as $user)
                <a href="{{ route('admin.users.show', $user->id) }}" class="block p-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex w-100 justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="text-sm font-semibold text-gray-900">{{ $user->name }}</h6>
                                <p class="text-[11px] text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <small class="text-[10px] text-gray-400 font-medium">{{ $user->created_at->diffForHumans() }}</small>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Orders Chart
    const ordersChart = document.getElementById('ordersChart');
    const ctx = ordersChart.getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(0, 166, 81, 0.15)');
    gradient.addColorStop(1, 'rgba(0, 166, 81, 0.0)');

    new Chart(ordersChart, {
        type: 'line',
        data: {
            labels: {!! json_encode($orderChartLabels) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode($orderChartData) !!},
                borderColor: '#00A651',
                backgroundColor: gradient,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#00A651',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
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
                        font: { family: 'Inter', size: 11 }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { family: 'Inter', size: 11 }
                    }
                }
            },
            maintainAspectRatio: false,
            responsive: true
        }
    });
</script>
@endsection