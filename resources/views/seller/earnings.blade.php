@extends('seller.layouts.app')

@section('page-title', 'Earnings')
@section('page-subtitle', 'Track revenue, order volume, and trends')

@section('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="animate-stagger">
    <!-- Earnings Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <!-- All-time earnings -->
        <div class="card-elevated p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-money-bill-wave text-emerald-500 text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">All-time Earnings</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-1 font-display">{{ number_format($allTimeEarnings['total']) }} <span class="text-sm text-gray-400 font-medium">PKR</span></p>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 flex items-center justify-center"><i class="fas fa-receipt text-emerald-400 text-[0.6rem]"></i></span>
                    {{ $allTimeEarnings['count'] }} completed orders
                </p>
            </div>
        </div>

        <!-- Period earnings -->
        <div class="card-elevated p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-check text-blue-500 text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ ucfirst($period) }} Earnings</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-1 font-display">{{ number_format($currentPeriodEarnings['total']) }} <span class="text-sm text-gray-400 font-medium">PKR</span></p>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-md bg-blue-50 flex items-center justify-center"><i class="fas fa-receipt text-blue-400 text-[0.6rem]"></i></span>
                    {{ $currentPeriodEarnings['count'] }} completed orders
                </p>
            </div>
        </div>

        <!-- Average per order -->
        <div class="card-elevated p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-amber-500/10 to-transparent rounded-full -mr-8 -mt-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chart-line text-amber-500 text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Avg. Per Order</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-1 font-display">
                    @if($allTimeEarnings['count'] > 0)
                        {{ number_format($allTimeEarnings['total'] / $allTimeEarnings['count']) }}
                    @else
                        0
                    @endif
                    <span class="text-sm text-gray-400 font-medium">PKR</span>
                </p>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-md bg-amber-50 flex items-center justify-center"><i class="fas fa-info text-amber-400 text-[0.6rem]"></i></span>
                    Based on completed orders
                </p>
            </div>
        </div>
    </div>

    <!-- Time Period Filter -->
    <div class="card-elevated p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-calendar text-primary"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Time Period</h3>
                    <p class="text-xs text-gray-400">Filter earnings by period</p>
                </div>
            </div>
            <form action="{{ route('seller.earnings') }}" method="GET" class="flex flex-wrap gap-2">
                @php
                    $periodOptions = ['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'];
                @endphp
                @foreach($periodOptions as $value => $label)
                    <button type="submit" name="period" value="{{ $value }}"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                            {{ $period === $value
                                ? 'bg-gradient-to-r from-primary to-primary-dark text-white shadow-md shadow-primary/20'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>
        </div>
    </div>

    <!-- Earnings Chart -->
    <div class="card-elevated overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <i class="fas fa-chart-bar text-indigo-500"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Earnings Trend</h3>
                    <p class="text-xs text-gray-400">Last 6 months</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="h-[320px]">
                <canvas id="earningsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Completed Orders Table -->
    <div class="card-elevated overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <i class="fas fa-clipboard-check text-emerald-500"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Recent Completed Orders</h3>
                <p class="text-xs text-gray-400">Earnings from delivered orders</p>
            </div>
        </div>
        <div>
            @if($completedOrders->isEmpty())
                <div class="text-center py-14">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-gray-300 text-2xl"></i>
                    </div>
                    <p class="text-gray-400 text-sm font-medium">No completed orders found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Order</th>
                                <th class="px-6 py-3.5 text-left text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3.5 text-left text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3.5 text-right text-[0.68rem] font-bold text-gray-400 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($completedOrders as $order)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-primary">#{{ $order->id }}</span>
                                        <p class="text-[0.65rem] text-gray-400 mt-0.5">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                        {{ $order->user?->name ?? 'Customer' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="space-y-1">
                                            @foreach($order->items as $item)
                                                <div class="flex items-center justify-between gap-4">
                                                    <span class="font-medium text-gray-800 text-xs">
                                                        {{ $item->product?->name ?? 'Product' }}
                                                    </span>
                                                    <span class="text-[0.7rem] text-gray-400 shrink-0">
                                                        {{ $item->quantity }} × {{ number_format($item->price) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                        {{ number_format($order->total_amount) }} <span class="text-xs text-gray-400 font-medium">PKR</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-100">
                    {{ $completedOrders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('earningsChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, 'rgba(0, 166, 81, 0.2)');
            gradient.addColorStop(1, 'rgba(0, 166, 81, 0.02)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($monthlyData)) !!},
                    datasets: [{
                        label: 'Monthly Earnings (PKR)',
                        data: {!! json_encode(array_values($monthlyData)) !!},
                        backgroundColor: gradient,
                        borderColor: '#00A651',
                        borderWidth: 2,
                        borderRadius: 12,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Inter', weight: '600' },
                            bodyFont: { family: 'Inter' },
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return 'PKR ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#9ca3af',
                                callback: function (value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#9ca3af',
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection