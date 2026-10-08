@extends('admin.layouts.app')

@section('title', 'Moderation Queue')

@section('styles')
    <style>
        .moderation-tab {
            transition: all 0.2s ease-in-out;
        }
        .moderation-tab:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection

@section('content')
    <div class="mb-8">
        <p class="text-sm text-gray-400 font-medium mb-1">Approvals</p>
        <h1 class="text-3xl font-extrabold text-gray-950 font-display tracking-tight">Moderation Queue</h1>
        <p class="text-gray-400 mt-1 text-sm">Review, verify, and approve pending accounts, products, and reviews in bulk.</p>
    </div>

    <!-- Stats Navigation Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('admin.moderation.queue', ['tab' => 'sellers', 'search' => $search]) }}"
            class="bg-white border rounded-2xl p-5 transition-all duration-200 shadow-sm hover:shadow {{ $tab === 'sellers' ? 'border-primary ring-2 ring-primary/10' : 'border-gray-100 hover:border-gray-200' }} moderation-tab flex items-center justify-between group relative overflow-hidden">
            <div class="stat-stripe {{ $tab === 'sellers' ? 'bg-gradient-to-b from-blue-400 to-blue-600' : 'bg-gray-200' }}"></div>
            <div class="pl-2">
                <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Pending Sellers</p>
                <p class="text-2xl font-extrabold text-gray-950 mt-1 font-display">{{ $counts['sellers'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 transition-colors group-hover:bg-blue-100">
                <i class="fas fa-store text-lg"></i>
            </div>
        </a>
        <a href="{{ route('admin.moderation.queue', ['tab' => 'reviews', 'search' => $search]) }}"
            class="bg-white border rounded-2xl p-5 transition-all duration-200 shadow-sm hover:shadow {{ $tab === 'reviews' ? 'border-primary ring-2 ring-primary/10' : 'border-gray-100 hover:border-gray-200' }} moderation-tab flex items-center justify-between group relative overflow-hidden">
            <div class="stat-stripe {{ $tab === 'reviews' ? 'bg-gradient-to-b from-yellow-400 to-yellow-600' : 'bg-gray-200' }}"></div>
            <div class="pl-2">
                <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Pending Reviews</p>
                <p class="text-2xl font-extrabold text-gray-950 mt-1 font-display">{{ $counts['reviews'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center text-yellow-600 transition-colors group-hover:bg-yellow-100">
                <i class="fas fa-star text-lg"></i>
            </div>
        </a>
        <a href="{{ route('admin.moderation.queue', ['tab' => 'riders', 'search' => $search]) }}"
            class="bg-white border rounded-2xl p-5 transition-all duration-200 shadow-sm hover:shadow {{ $tab === 'riders' ? 'border-primary ring-2 ring-primary/10' : 'border-gray-100 hover:border-gray-200' }} moderation-tab flex items-center justify-between group relative overflow-hidden">
            <div class="stat-stripe {{ $tab === 'riders' ? 'bg-gradient-to-b from-amber-400 to-amber-600' : 'bg-gray-200' }}"></div>
            <div class="pl-2">
                <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Pending Riders</p>
                <p class="text-2xl font-extrabold text-gray-950 mt-1 font-display">{{ $counts['riders'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 transition-colors group-hover:bg-amber-100">
                <i class="fas fa-motorcycle text-lg"></i>
            </div>
        </a>
        <a href="{{ route('admin.moderation.queue', ['tab' => 'products', 'search' => $search]) }}"
            class="bg-white border rounded-2xl p-5 transition-all duration-200 shadow-sm hover:shadow {{ $tab === 'products' ? 'border-primary ring-2 ring-primary/10' : 'border-gray-100 hover:border-gray-200' }} moderation-tab flex items-center justify-between group relative overflow-hidden">
            <div class="stat-stripe {{ $tab === 'products' ? 'bg-gradient-to-b from-emerald-400 to-emerald-600' : 'bg-gray-200' }}"></div>
            <div class="pl-2">
                <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Pending Products</p>
                <p class="text-2xl font-extrabold text-gray-950 mt-1 font-display">{{ $counts['products'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-primary transition-colors group-hover:bg-green-100">
                <i class="fas fa-box-open text-lg"></i>
            </div>
        </a>
    </div>

    <!-- Search / Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 p-5">
        <form method="GET" action="{{ route('admin.moderation.queue') }}" class="flex flex-col md:flex-row gap-3 md:items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search queues by name, email, phone, details..."
                    class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition-all duration-200 shadow hover:shadow-md">
                    Search
                </button>
                <a href="{{ route('admin.moderation.queue', ['tab' => $tab]) }}"
                    class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Moderation List Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($tab === 'sellers')
            <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-950 flex items-center gap-2"><i class="fas fa-store text-blue-500"></i> Seller Registration Requests</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Approve or reject pending seller profile applications.</p>
                </div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-lg">Tab: Sellers</span>
            </div>
            @if($pendingSellers->isEmpty())
                <div class="p-16 text-center text-gray-400">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-double text-gray-300 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium">No pending seller registration requests.</p>
                </div>
            @else
                <form method="POST" action="{{ route('admin.moderation.bulk') }}">
                    @csrf
                    <input type="hidden" name="tab" value="sellers">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="p-4 border-b border-gray-50 bg-white flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex items-center text-sm text-gray-700 gap-2 cursor-pointer font-medium">
                            <input type="checkbox" class="bulk-select-all rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                            Select all on this page
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="action" value="approve"
                                class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-bold hover:bg-green-100 transition-colors border border-green-200">
                                <i class="fas fa-check mr-1"></i> Approve Selected
                            </button>
                            <button type="submit" name="action" value="reject"
                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition-colors border border-red-200">
                                <i class="fas fa-times mr-1"></i> Reject Selected
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-50">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Select</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Merchant Name</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email Address</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Submitted</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach($pendingSellers as $seller)
                                    <tr class="hover:bg-gray-50/20 transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="ids[]" value="{{ $seller->id }}"
                                                class="bulk-item-checkbox rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap font-bold text-gray-950 text-sm">
                                            {{ $seller->name }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">{{ $seller->email }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ optional($seller->created_at)->diffForHumans() }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="submit" name="action" value="approve"
                                                    onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $seller->id }}\']').checked = true;"
                                                    class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 border border-green-100 transition-colors">
                                                    Approve
                                                </button>
                                                <button type="submit" name="action" value="reject"
                                                    onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $seller->id }}\']').checked = true;"
                                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 border border-red-100 transition-colors">
                                                    Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                @if($pendingSellers->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">{{ $pendingSellers->links() }}</div>
                @endif
            @endif
        @endif

        @if($tab === 'riders')
            <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-950 flex items-center gap-2"><i class="fas fa-motorcycle text-amber-500"></i> Rider Application Requests</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Verify and approve new courier profile registrations.</p>
                </div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-lg">Tab: Riders</span>
            </div>
            @if($pendingRiders->isEmpty())
                <div class="p-16 text-center text-gray-400">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-double text-gray-300 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium">No pending rider application requests.</p>
                </div>
            @else
                <form method="POST" action="{{ route('admin.moderation.bulk') }}">
                    @csrf
                    <input type="hidden" name="tab" value="riders">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="p-4 border-b border-gray-50 bg-white flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex items-center text-sm text-gray-700 gap-2 cursor-pointer font-medium">
                            <input type="checkbox" class="bulk-select-all rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                            Select all on this page
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="action" value="approve"
                                class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-bold hover:bg-green-100 transition-colors border border-green-200">
                                <i class="fas fa-check mr-1"></i> Approve Selected
                            </button>
                            <button type="submit" name="action" value="reject"
                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition-colors border border-red-200">
                                <i class="fas fa-times mr-1"></i> Reject Selected
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-50">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Select</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rider Profile</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Contact Info</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Vehicle Details</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach($pendingRiders as $rider)
                                    <tr class="hover:bg-gray-50/20 transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="ids[]" value="{{ $rider->id }}"
                                                class="bulk-item-checkbox rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-950">{{ $rider->name }}</div>
                                            <div class="text-[11px] text-gray-400">{{ $rider->email }}</div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div>{{ $rider->phone }}</div>
                                            <div class="text-[11px] text-gray-400 truncate max-w-xs">{{ $rider->address }}</div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold">
                                            {{ $rider->vehicle_type }} ({{ $rider->vehicle_number }})
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="submit" name="action" value="approve"
                                                    onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $rider->id }}\']').checked = true;"
                                                    class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 border border-green-100 transition-colors">
                                                    Approve
                                                </button>
                                                <button type="submit" name="action" value="reject"
                                                    onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $rider->id }}\']').checked = true;"
                                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 border border-red-100 transition-colors">
                                                    Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                @if($pendingRiders->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">{{ $pendingRiders->links() }}</div>
                @endif
            @endif
        @endif

        @if($tab === 'products')
            <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-950 flex items-center gap-2"><i class="fas fa-box-open text-green-500"></i> Product Approval Requests</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Approve new product listing offers created by merchants.</p>
                </div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-lg">Tab: Products</span>
            </div>
            @if($pendingProducts->isEmpty())
                <div class="p-16 text-center text-gray-400">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-double text-gray-300 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium">No pending product approval requests.</p>
                </div>
            @else
                <form method="POST" action="{{ route('admin.moderation.bulk') }}">
                    @csrf
                    <input type="hidden" name="tab" value="products">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="p-4 border-b border-gray-50 bg-white flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex items-center text-sm text-gray-700 gap-2 cursor-pointer font-medium">
                            <input type="checkbox" class="bulk-select-all rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                            Select all on this page
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="action" value="approve"
                                class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-bold hover:bg-green-100 transition-colors border border-green-200">
                                <i class="fas fa-check mr-1"></i> Approve Selected
                            </button>
                            <button type="submit" name="action" value="reject"
                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 transition-colors border border-red-200">
                                <i class="fas fa-times mr-1"></i> Reject Selected
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-50">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Select</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Product</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seller Profile</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Offer Price</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach($pendingProducts as $product)
                                    <tr class="hover:bg-gray-50/20 transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="ids[]" value="{{ $product->id }}"
                                                class="bulk-item-checkbox rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-950">{{ $product->name }}</div>
                                            <div class="text-[11px] text-gray-400 truncate max-w-xs">{{ $product->description }}</div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold">
                                            {{ optional($product->seller)->name ?? 'Unknown Merchant' }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ number_format($product->price ?? 0, 2) }} PKR
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="submit" name="action" value="approve"
                                                    onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $product->id }}\']').checked = true;"
                                                    class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 border border-green-100 transition-colors">
                                                    Approve
                                                </button>
                                                <button type="submit" name="action" value="reject"
                                                    onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $product->id }}\']').checked = true;"
                                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 border border-red-100 transition-colors">
                                                    Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                @if($pendingProducts->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">{{ $pendingProducts->links() }}</div>
                @endif
            @endif
        @endif

        @if($tab === 'reviews')
            <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-950 flex items-center gap-2"><i class="fas fa-star text-yellow-500"></i> Review Moderation Queue</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Filter, audit, and approve customer review logs before publishing.</p>
                </div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-lg">Tab: Reviews</span>
            </div>
            @if($pendingReviews->isEmpty())
                <div class="p-16 text-center text-gray-400">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-double text-gray-300 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium">No pending reviews.</p>
                </div>
            @else
                <form method="POST" action="{{ route('admin.moderation.bulk') }}">
                    @csrf
                    <input type="hidden" name="tab" value="reviews">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="p-4 border-b border-gray-50 bg-white flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex items-center text-sm text-gray-700 gap-2 cursor-pointer font-medium">
                            <input type="checkbox" class="bulk-select-all rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4">
                            Select all on this page
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="submit" name="action" value="approve" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-bold hover:bg-green-100 border border-green-200 transition-colors">
                                <i class="fas fa-check mr-1"></i> Approve Selected
                            </button>
                            <button type="submit" name="action" value="reject" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-bold hover:bg-red-100 border border-red-200 transition-colors">
                                <i class="fas fa-times mr-1"></i> Reject Selected
                            </button>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($pendingReviews as $review)
                            <div class="p-5 hover:bg-gray-50/30 transition-colors">
                                <div class="flex items-start justify-between gap-4">
                                    <label class="inline-flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="ids[]" value="{{ $review->id }}" class="bulk-item-checkbox rounded border-gray-300 text-primary focus:ring-primary/20 h-4 w-4 mt-0.5">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-gray-950">{{ $review->user?->name ?? 'Customer' }}</span>
                                                <div class="flex items-center text-amber-500 text-xs gap-0.5">
                                                    @for($r = 1; $r <= 5; $r++)
                                                        <i class="fa{{ $r <= $review->rating ? 's' : 'r' }} fa-star"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-2 font-medium bg-gray-50 p-4 rounded-xl border border-gray-100 whitespace-pre-line leading-relaxed max-w-3xl">
                                                "{{ $review->feedback }}"
                                            </p>
                                            <div class="flex items-center gap-x-3 gap-y-1 text-[11px] text-gray-400 mt-2">
                                                <span>Merchant: <strong class="text-gray-700 font-semibold">{{ $review->seller?->name ?? 'Unknown' }}</strong></span>
                                                <span>•</span>
                                                <span>Order: <strong class="text-gray-700 font-semibold">#{{ $review->order_id }}</strong></span>
                                            </div>
                                        </div>
                                    </label>

                                    <div class="flex gap-2 flex-shrink-0">
                                        <button type="submit" name="action" value="approve"
                                            onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $review->id }}\']').checked = true;"
                                            class="px-2.5 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 border border-green-100 transition-colors">
                                            Approve
                                        </button>
                                        <button type="submit" name="action" value="reject"
                                            onclick="this.form.querySelectorAll('.bulk-item-checkbox').forEach(cb => cb.checked = false); this.form.querySelector('input[name=\'ids[]\'][value=\'{{ $review->id }}\']').checked = true;"
                                            class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 border border-red-100 transition-colors">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
                @if($pendingReviews->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">{{ $pendingReviews->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.bulk-select-all').forEach((selectAllCheckbox) => {
            const form = selectAllCheckbox.closest('form');
            if (!form) return;

            selectAllCheckbox.addEventListener('change', () => {
                form.querySelectorAll('.bulk-item-checkbox').forEach((itemCheckbox) => {
                    itemCheckbox.checked = selectAllCheckbox.checked;
                });
            });
        });
    </script>
@endsection
