@extends('seller.layouts.app')

@section('page-title', 'Add New Product')
@section('page-subtitle', 'Create a new product for your customers')

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
                    <p class="text-xs text-gray-400">Fill in the details below to list your product</p>
                </div>
            </div>
            <a href="{{ route('seller.panel') }}" class="btn-secondary-custom text-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- Form Card -->
        <div class="card-elevated overflow-hidden">
            <form method="POST" action="{{ route('store.service') }}" enctype="multipart/form-data" id="addProductForm">
                @csrf

                <!-- Basic Info Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xs font-bold">1</span>
                        <h3 class="text-sm font-bold text-gray-900">Basic Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="seller_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Seller Name</label>
                            <input type="text" id="seller_name" value="{{ $seller->name }}" readonly
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label for="service_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Product Name <span class="text-red-400">*</span></label>
                            <input type="text" id="service_name" name="service_name" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="Enter product name">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="service_description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description <span class="text-red-400">*</span></label>
                        <textarea id="service_description" name="service_description" rows="4" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none"
                            placeholder="Describe your product..."></textarea>
                    </div>
                </div>

                <!-- Location & Contact Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 text-xs font-bold">2</span>
                        <h3 class="text-sm font-bold text-gray-900">Location & Contact</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="seller_city" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">City <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_city" name="seller_city" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="e.g. Karachi">
                        </div>
                        <div>
                            <label for="seller_area" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Area <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_area" name="seller_area" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="e.g. Gulshan-e-Iqbal">
                        </div>
                        <div>
                            <label for="availability" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Availability <span class="text-red-400">*</span></label>
                            <input type="text" id="availability" name="availability" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="e.g. Mon-Fri, 9AM-5PM">
                        </div>
                        <div>
                            <label for="seller_contact_no" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Contact Number <span class="text-red-400">*</span></label>
                            <input type="text" id="seller_contact_no" name="seller_contact_no" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="0300-1234567">
                        </div>
                    </div>
                </div>

                <!-- Pricing & Media Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 text-xs font-bold">3</span>
                        <h3 class="text-sm font-bold text-gray-900">Pricing & Media</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="service_price" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Price (PKR) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">PKR</span>
                                <input type="number" id="service_price" name="service_price" min="1" required
                                    class="w-full border border-gray-200 rounded-xl pl-14 pr-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label for="service_delivery_time" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Delivery Time <span class="text-red-400">*</span></label>
                            <input type="text" id="service_delivery_time" name="service_delivery_time" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                placeholder="e.g. 2-3 business days">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Product Image <span class="text-red-400">*</span></label>
                        <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-primary/40 transition-colors cursor-pointer group" id="dropZone">
                            <input type="file" id="image" name="image" accept="image/*" required class="hidden">
                            <div class="flex flex-col items-center" id="uploadPlaceholder">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mb-3 group-hover:bg-primary/5 transition-colors">
                                    <i class="fas fa-cloud-upload-alt text-gray-300 text-2xl group-hover:text-primary/50 transition-colors"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-600 mb-1">Click to upload or drag & drop</p>
                                <p class="text-xs text-gray-400">PNG, JPG up to 5MB</p>
                            </div>
                            <div class="hidden" id="uploadPreview">
                                <img id="previewImage" class="w-24 h-24 object-cover rounded-xl mx-auto mb-3 border-2 border-primary/20" alt="Preview">
                                <p class="text-sm font-semibold text-primary" id="fileName"></p>
                            </div>
                        </div>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('image');
        const placeholder = document.getElementById('uploadPlaceholder');
        const preview = document.getElementById('uploadPreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary/40', 'bg-primary/5');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-primary/40', 'bg-primary/5');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary/40', 'bg-primary/5');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showPreview(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length) showPreview(this.files[0]);
        });

        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                fileName.textContent = file.name;
                placeholder.classList.add('hidden');
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
