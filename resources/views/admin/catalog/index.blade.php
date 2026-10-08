@extends('admin.layouts.app')

@section('title', 'Master Catalog')

@section('content')
    <div class="mb-8">
        <p class="text-sm text-gray-400 font-medium mb-1">Management</p>
        <h1 class="text-3xl font-extrabold text-gray-950 font-display tracking-tight">Master Catalog</h1>
        <p class="text-gray-400 mt-1 text-sm">Manage category templates and global master products that sellers can import into their storefronts.</p>
    </div>

    <!-- Forms Section: Add Category and Add Product -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Add Category Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                    <i class="fas fa-folder-plus text-primary"></i> Add Category
                </h3>
                <p class="text-xs text-gray-400 mb-5">Create a new category template for master products</p>

                <form method="POST" action="{{ route('admin.catalog.categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Category Name</label>
                        <input type="text" name="name" required placeholder="e.g. Fresh Vegetables"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Description</label>
                        <textarea name="description" rows="3" placeholder="Describe this category template..."
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all"></textarea>
                    </div>
                    <div>
                        <button type="submit" class="w-full py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow hover:shadow-md">
                            Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Master Product Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 xl:col-span-2 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-primary"></i> Add Master Product
                </h3>
                <p class="text-xs text-gray-400 mb-5">Add a standardized catalog product templates that merchants can load</p>

                <form method="POST" action="{{ route('admin.catalog.products.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Template Category</label>
                        <select name="catalog_category_id" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Product Name</label>
                        <input type="text" name="name" required placeholder="e.g. Farm Fresh Milk (1L)"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Base Price (PKR)</label>
                        <input type="number" name="base_price" step="0.01" min="0" required placeholder="e.g. 240"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Unit Type</label>
                        <input type="text" name="unit_type" required placeholder="e.g. bottle, kg, packet"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Default Product Image</label>
                        <input type="file" name="default_image"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none bg-gray-50/50 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Description</label>
                        <textarea name="description" rows="2" placeholder="Detail specifications, brands, or descriptions..."
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow hover:shadow-md">
                            Add Master Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Products Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Header & Category Switcher -->
        <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-layer-group text-primary"></i> Template Products
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Active products inside: <strong class="text-primary">{{ $activeCategory ? $activeCategory->name : 'None' }}</strong></p>
            </div>
            <div class="w-full sm:w-auto">
                <form method="GET" action="{{ route('admin.catalog.index') }}">
                    <select name="category_id" onchange="this.form.submit()"
                        class="w-full sm:w-64 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white font-semibold text-gray-700 transition-all">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $activeCategory && $activeCategory->id === $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Product</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Price</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unit</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex items-center justify-center">
                                        @if($product->default_image)
                                            <img src="{{ asset('storage/' . $product->default_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <i class="fas fa-image text-gray-300"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-950 text-sm">{{ $product->name }}</div>
                                        <div class="text-[11px] text-gray-400 max-w-sm truncate">{{ $product->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ number_format($product->base_price, 2) }} PKR
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $product->unit_type ?? 'Item' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($product->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-green-50 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-red-50 text-red-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" class="px-3 py-1.5 text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors flex items-center gap-1 shadow-sm"
                                        onclick="toggleModal('editProductModal{{ $product->id }}')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.catalog.products.destroy', $product) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this master product?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-red-50 hover:bg-red-100 text-red-700 rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Master Product Modal -->
                        <div id="editProductModal{{ $product->id }}" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="toggleModal('editProductModal{{ $product->id }}')"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                                    <form method="POST" action="{{ route('admin.catalog.products.update', $product) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="catalog_category_id" value="{{ $product->catalog_category_id }}">

                                        <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                                <h3 class="text-lg font-bold text-gray-900">Edit Product Template</h3>
                                                <button type="button" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors" onclick="toggleModal('editProductModal{{ $product->id }}')">
                                                    <i class="fas fa-times text-sm"></i>
                                                </button>
                                            </div>

                                            <div class="mt-6 space-y-4">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Product Name</label>
                                                    <input type="text" name="name" value="{{ $product->name }}" required
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                                                </div>

                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Base Price (PKR)</label>
                                                        <input type="number" name="base_price" step="0.01" min="0" value="{{ $product->base_price }}" required
                                                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Unit Type</label>
                                                        <input type="text" name="unit_type" value="{{ $product->unit_type }}" required
                                                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Replace Product Image</label>
                                                    <input type="file" name="default_image"
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none bg-gray-50/50 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                                </div>

                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Description</label>
                                                    <textarea name="description" rows="3"
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-gray-50/50 hover:bg-white transition-all">{{ $product->description }}</textarea>
                                                </div>

                                                <div class="space-y-2 pt-2">
                                                    <label class="relative flex items-start">
                                                        <div class="flex items-center h-5">
                                                            <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}
                                                                class="rounded border-gray-300 text-primary focus:ring-primary/30 h-4 w-4">
                                                        </div>
                                                        <div class="ml-3 text-sm">
                                                            <span class="font-semibold text-gray-700">Active Status</span>
                                                            <p class="text-xs text-gray-400">Determines if this template is active and visible to sellers.</p>
                                                        </div>
                                                    </label>

                                                    <label class="relative flex items-start mt-3">
                                                        <div class="flex items-center h-5">
                                                            <input type="checkbox" name="push_to_non_custom_prices" value="1"
                                                                class="rounded border-gray-300 text-primary focus:ring-primary/30 h-4 w-4">
                                                        </div>
                                                        <div class="ml-3 text-sm">
                                                            <span class="font-semibold text-gray-700">Push default price updates</span>
                                                            <p class="text-xs text-gray-400">Push this new base price to all active sellers using non-custom pricing.</p>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow px-4 py-2 bg-primary text-sm font-semibold text-white hover:bg-primary-dark focus:outline-none sm:ml-3 sm:w-auto">
                                                Save Changes
                                            </button>
                                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto"
                                                onclick="toggleModal('editProductModal{{ $product->id }}')">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-boxes text-lg text-gray-300"></i>
                                </div>
                                <p class="text-sm font-medium">No products registered in this category.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            }
        }
    </script>
@endsection
