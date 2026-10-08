@extends('seller.layouts.app')

@section('page-title', 'Add New Product')
@section('page-subtitle', 'Create a new product listing')

@section('content')
<div class="animate-stagger">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-primary/10 to-emerald-100 flex items-center justify-center">
                    <i class="fas fa-plus-circle text-primary text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 font-display">Add New Product</h2>
                    <p class="text-xs text-gray-400">Fill in the details to list your product</p>
                </div>
            </div>
            <a href="{{ route('seller.panel') }}" class="btn-secondary-custom text-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        @if ($errors->any())
            <div class="flash-message flash-error mb-6">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="card-elevated overflow-hidden">
            <form method="POST" action="{{ route('store.service') }}" enctype="multipart/form-data">
                @csrf

                <!-- Product Details -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xs font-bold">1</span>
                        <h3 class="text-sm font-bold text-gray-900">Product Details</h3>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Product Name <span class="text-red-400">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('name') border-red-300 @enderror"
                                placeholder="Enter product name">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description <span class="text-red-400">*</span></label>
                            <textarea id="description" name="description" rows="3" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none @error('description') border-red-300 @enderror"
                                placeholder="Describe your product...">{{ old('description') }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                                <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" required
                                    class="w-full border border-gray-200 rounded-xl pl-14 pr-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('price') border-red-300 @enderror">
                            </div>
                            @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="stock_quantity" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Stock Quantity <span class="text-red-400">*</span></label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity') }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('stock_quantity') border-red-300 @enderror">
                            @error('stock_quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="unit_type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Unit Type <span class="text-red-400">*</span></label>
                            <input type="text" id="unit_type" name="unit_type" value="{{ old('unit_type') }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('unit_type') border-red-300 @enderror"
                                placeholder="e.g. kg, pcs, dozen">
                            @error('unit_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Location & Image -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 text-xs font-bold">3</span>
                        <h3 class="text-sm font-bold text-gray-900">Location & Image</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label for="seller_city" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">City <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_city" name="seller_city"
                                value="{{ old('seller_city', Auth::guard('seller')->user()->city) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            @error('seller_city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="seller_area" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Area <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_area" name="seller_area"
                                value="{{ old('seller_area', Auth::guard('seller')->user()->area) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            @error('seller_area') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="seller_contact_no" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Contact No <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_contact_no" name="seller_contact_no"
                                value="{{ old('seller_contact_no', Auth::guard('seller')->user()->phone ?? '') }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="0300-1234567">
                            @error('seller_contact_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Product Image <span class="text-red-400">*</span></label>
                        <input type="file" id="image" name="image" accept="image/*" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer @error('image') border-red-300 @enderror">
                        @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Submit -->
                <div class="p-6 bg-gray-50/50 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('seller.panel') }}" class="btn-secondary-custom justify-center">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-primary-custom justify-center">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection