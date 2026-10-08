@extends('seller.layouts.app')

@section('page-title', 'Edit Product')
@section('page-subtitle', 'Update product details')

@section('content')
<div class="animate-stagger">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                    <i class="fas fa-edit text-blue-500 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 font-display">Edit Product</h2>
                    <p class="text-xs text-gray-400">Update the details of your product</p>
                </div>
            </div>
            <a href="{{ route('seller.panel') }}" class="btn-secondary-custom text-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- Form Card -->
        <div class="card-elevated overflow-hidden">
            <form method="POST" action="{{ route('seller.updateService', $product->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Product Details -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xs font-bold">1</span>
                        <h3 class="text-sm font-bold text-gray-900">Product Details</h3>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Product Name <span class="text-red-400">*</span></label>
                            <input type="text" id="name" name="name" value="{{ $product->name }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description <span class="text-red-400">*</span></label>
                            <textarea id="description" name="description" rows="3" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none">{{ $product->description }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 text-xs font-bold">2</span>
                        <h3 class="text-sm font-bold text-gray-900">Pricing & Stock</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label for="price" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Price (PKR) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">PKR</span>
                                <input type="number" id="price" name="price" step="0.01" value="{{ $product->price }}" required
                                    class="w-full border border-gray-200 rounded-xl pl-14 pr-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label for="stock_quantity" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Stock Quantity <span class="text-red-400">*</span></label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="{{ $product->stock_quantity }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label for="unit_type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Unit Type <span class="text-red-400">*</span></label>
                            <input type="text" id="unit_type" name="unit_type" value="{{ $product->unit_type }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="e.g. kg, pcs">
                        </div>
                    </div>
                </div>

                <!-- Image -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 text-xs font-bold">3</span>
                        <h3 class="text-sm font-bold text-gray-900">Product Image</h3>
                    </div>

                    <div class="flex items-start gap-6">
                        @if($product->image)
                            <div class="flex-shrink-0">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current Image</p>
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="w-24 h-24 object-cover rounded-xl border-2 border-gray-100">
                            </div>
                        @endif
                        <div class="flex-1">
                            <label for="image" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Change Image (Optional)</label>
                            <input type="file" id="image" name="image" accept="image/*"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-1.5">Leave empty to keep the current image</p>
                        </div>
                    </div>
                </div>

                <!-- Hidden legacy fields -->
                <input type="hidden" name="seller_city" value="{{ $product->seller_city }}">
                <input type="hidden" name="seller_area" value="{{ $product->seller_area }}">
                <input type="hidden" name="seller_contact_no" value="{{ $product->seller_contact_no }}">

                <!-- Submit -->
                <div class="p-6 bg-gray-50/50 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('seller.panel') }}" class="btn-secondary-custom justify-center">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-primary-custom justify-center">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection