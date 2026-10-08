@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 font-display">Settings</h1>
        <p class="text-gray-600 mt-1">Manage checkout promo codes and platform controls.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="p-5 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Create Promo Code</h3>
        </div>
        <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="p-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                <input name="code" value="{{ old('code') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="BAKALA20" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="discount_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="fixed">Fixed PKR</option>
                    <option value="percent">Percent</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                <input type="number" step="0.01" min="0.01" name="discount_value" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Order</label>
                <input type="number" step="0.01" min="0" name="minimum_order_amount" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Discount</label>
                <input type="number" step="0.01" min="0" name="maximum_discount_amount" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                <input type="number" min="1" name="usage_limit" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires</label>
                <input type="datetime-local" name="expires_at" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
            <button type="submit" class="md:col-span-4 px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark">
                Create Promo Code
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Promo Codes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Code</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Discount</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Used</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($promoCodes as $promo)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $promo->code }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $promo->discount_type === 'percent' ? $promo->discount_value . '%' : 'PKR ' . number_format($promo->discount_value, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $promo->used_count }}{{ $promo->usage_limit ? ' / ' . $promo->usage_limit : '' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $promo->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $promo->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No promo codes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($promoCodes->hasPages())
            <div class="p-4 border-t">{{ $promoCodes->links() }}</div>
        @endif
    </div>
@endsection
