@extends('seller.layouts.app')

@section('page-title', 'My Catalog')
@section('page-subtitle', 'Manage your product listings and pricing')

@section('content')
<div class="animate-stagger">
    <!-- Header with Category -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100 flex items-center justify-center">
                <i class="fas fa-layer-group text-violet-500 text-lg"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900 font-display">My Catalog Listings</h2>
                <p class="text-xs text-gray-400">Category: <span class="font-semibold text-gray-600">{{ $seller->catalogCategory?->name ?? 'Not assigned' }}</span></p>
            </div>
        </div>
    </div>

    <!-- Import Card -->
    <div class="card-elevated p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-download text-blue-500"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">Master items available</p>
                    <p class="text-xs text-gray-400">{{ $availableInCategoryCount }} products in your category</p>
                </div>
            </div>
            <form method="POST" action="{{ route('seller.catalog.import') }}">
                @csrf
                <button type="submit" class="btn-primary-custom text-sm">
                    <i class="fas fa-download"></i> One-Click Import
                </button>
            </form>
        </div>
    </div>

    <!-- Search -->
    <div class="card-elevated p-5 mb-6">
        <form method="GET" action="{{ route('seller.catalog.index') }}" class="flex items-center gap-3 max-w-lg">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products by name..."
                    class="w-full border border-gray-200 rounded-xl pl-11 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
            </div>
            <button type="submit" class="btn-primary-custom text-sm py-2.5">Search</button>
            @if(request('search'))
                <a href="{{ route('seller.catalog.index') }}" class="btn-secondary-custom text-sm py-2.5">Clear</a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="card-elevated p-5 mb-6" id="bulkActionsBar">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-bolt text-amber-500 text-sm"></i>
            </div>
            <h3 class="text-sm font-bold text-gray-900">Bulk Actions</h3>
        </div>
        <form id="bulk-action-form" onsubmit="return confirm('Are you sure you want to apply this action to the selected items?')" method="POST" action="{{ route('seller.catalog.bulk-update') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-[0.65rem] text-gray-400 uppercase font-semibold tracking-wide mb-1.5">Action</label>
                <select name="bulk_action" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    <option value="activate_all">Activate Selected</option>
                    <option value="deactivate_all">Deactivate Selected</option>
                    <option value="increase_percent">Increase Price by %</option>
                    <option value="decrease_percent">Decrease Price by %</option>
                    <option value="reset_to_base_price">Reset to Base Price</option>
                </select>
            </div>
            <div>
                <label class="block text-[0.65rem] text-gray-400 uppercase font-semibold tracking-wide mb-1.5">Percent (for price actions)</label>
                <input type="number" name="percent_value" step="0.01" min="0" placeholder="e.g. 10"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary-custom text-sm">
                    <i class="fas fa-play"></i> Apply Bulk Action
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="card-elevated overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">Update Prices and Listing Status</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-4 py-3.5 text-left w-12">
                            <input type="checkbox" id="select-all-listings" class="rounded border-gray-300 text-primary focus:ring-primary/20 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5 text-left text-[0.68rem] uppercase text-gray-400 font-bold tracking-wider">Product</th>
                        <th class="px-4 py-3.5 text-left text-[0.68rem] uppercase text-gray-400 font-bold tracking-wider">Base Price</th>
                        <th class="px-4 py-3.5 text-left text-[0.68rem] uppercase text-gray-400 font-bold tracking-wider">Custom Price</th>
                        <th class="px-4 py-3.5 text-left text-[0.68rem] uppercase text-gray-400 font-bold tracking-wider">Stock</th>
                        <th class="px-4 py-3.5 text-left text-[0.68rem] uppercase text-gray-400 font-bold tracking-wider">Status</th>
                        <th class="px-4 py-3.5 text-right text-[0.68rem] uppercase text-gray-400 font-bold tracking-wider">Save</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($listings as $listing)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-4 py-4">
                                <input type="checkbox" name="listing_ids[]" value="{{ $listing->id }}" class="listing-checkbox rounded border-gray-300 text-primary focus:ring-primary/20 cursor-pointer" form="bulk-action-form">
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $listing->globalProduct?->name ?? 'Unknown product' }}</div>
                                <div class="text-[0.7rem] text-gray-400">{{ $listing->globalProduct?->unit_type ?? 'Item' }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 font-medium">PKR {{ number_format((float) ($listing->globalProduct?->base_price ?? 0), 2) }}</td>
                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('seller.catalog.listings.update', $listing) }}" class="flex items-center justify-end gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" step="0.01" min="0" name="custom_price"
                                        value="{{ $listing->custom_price }}"
                                        placeholder="Use base"
                                        class="border border-gray-200 rounded-xl px-3 py-2 text-sm w-28 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            </td>
                            <td class="px-4 py-4">
                                    <input type="number" min="0" name="stock_quantity"
                                        value="{{ $listing->stock_quantity }}"
                                        class="border border-gray-200 rounded-xl px-3 py-2 text-sm w-20 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            </td>
                            <td class="px-4 py-4">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="is_active" value="1" {{ $listing->is_active ? 'checked' : '' }}
                                            class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:bg-primary transition-colors"></div>
                                        <div class="absolute left-[2px] top-[2px] bg-white w-4 h-4 rounded-full shadow-sm transition-transform peer-checked:translate-x-4"></div>
                                    </div>
                                    <span class="text-xs font-semibold {{ $listing->is_active ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ $listing->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </label>
                            </td>
                            <td class="px-4 py-4 text-right">
                                    <button type="submit" class="px-3 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl text-xs font-bold hover:shadow-md hover:shadow-primary/20 transition-all">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-box-open text-gray-300 text-2xl"></i>
                                </div>
                                <p class="text-gray-400 text-sm font-medium">No imported catalog items yet. Click import above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($listings->hasPages())
            <div class="p-5 border-t border-gray-100">{{ $listings->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('select-all-listings')?.addEventListener('change', function(e) {
        document.querySelectorAll('.listing-checkbox').forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endsection
