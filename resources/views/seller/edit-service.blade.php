@extends('seller.layouts.app')

@section('page-title', 'Edit Service')
@section('page-subtitle', 'Update service details')

@section('content')
<div class="animate-stagger">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100 flex items-center justify-center">
                    <i class="fas fa-edit text-violet-500 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 font-display">Edit Service</h2>
                    <p class="text-xs text-gray-400">Update the details of your service</p>
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
            <form action="{{ route('seller.updateService', $service->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Service Details -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xs font-bold">1</span>
                        <h3 class="text-sm font-bold text-gray-900">Service Details</h3>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="service_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Service Name <span class="text-red-400">*</span></label>
                            <input type="text" id="service_name" name="service_name"
                                value="{{ old('service_name', $service->service_name) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label for="service_description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description <span class="text-red-400">*</span></label>
                            <textarea id="service_description" name="service_description" rows="4" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none">{{ old('service_description', $service->service_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Location & Availability -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 text-xs font-bold">2</span>
                        <h3 class="text-sm font-bold text-gray-900">Location & Availability</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="seller_city" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">City <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_city" name="seller_city"
                                value="{{ old('seller_city', $service->seller_city) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label for="seller_area" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Area <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_area" name="seller_area"
                                value="{{ old('seller_area', $service->seller_area) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label for="availability" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Availability <span class="text-red-400">*</span></label>
                            <input type="text" id="availability" name="availability"
                                value="{{ old('availability', $service->availability) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label for="service_delivery_time" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Delivery Time <span class="text-red-400">*</span></label>
                            <input type="text" id="service_delivery_time" name="service_delivery_time"
                                value="{{ old('service_delivery_time', $service->service_delivery_time) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- Pricing & Image -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 text-xs font-bold">3</span>
                        <h3 class="text-sm font-bold text-gray-900">Pricing & Image</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="seller_contact_no" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Contact Number <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_contact_no" name="seller_contact_no"
                                value="{{ old('seller_contact_no', $service->seller_contact_no) }}" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label for="service_price" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Price (PKR) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">PKR</span>
                                <input type="number" id="service_price" name="service_price"
                                    value="{{ old('service_price', $service->service_price) }}" step="0.01" required
                                    class="w-full border border-gray-200 rounded-xl pl-14 pr-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-6">
                        @if($service->image)
                            <div class="flex-shrink-0">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current Image</p>
                                <img src="{{ asset('storage/' . $service->image) }}" alt="Current service image"
                                    class="w-24 h-24 object-cover rounded-xl border-2 border-gray-100">
                            </div>
                        @endif
                        <div class="flex-1">
                            <label for="image" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Change Image (Optional)</label>
                            <input type="file" id="image" name="image"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-1.5">Leave empty to keep current image</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="p-6 bg-gray-50/50 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('seller.panel') }}" class="btn-secondary-custom justify-center">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-primary-custom justify-center">
                        <i class="fas fa-save"></i> Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection