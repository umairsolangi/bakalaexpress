@extends('admin.layouts.app')

@section('title', 'Manage Products')

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8 gap-4">
        <div>
            <p class="text-sm text-gray-400 font-medium mb-1">Management</p>
            <h1 class="text-3xl font-extrabold text-gray-950 font-display tracking-tight">Manage Products</h1>
            <p class="text-gray-400 mt-1 text-sm">Review, search, and approve or audit product listings created by merchants.</p>
        </div>
    </div>

    <!-- Products Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Card Header with Search -->
        <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-box text-primary"></i> Registered Products
            </h3>
            <div class="w-full sm:w-auto">
                <form class="flex gap-2 w-full sm:w-auto" method="GET" action="{{ route('admin.products') }}">
                    <div class="relative flex-1 sm:flex-none">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, seller..."
                            class="pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary w-full sm:w-64 transition-all bg-gray-50/50 hover:bg-white">
                    </div>
                    <button class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow hover:shadow-md" type="submit">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.products') }}" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="p-0">
            @if($products->isEmpty())
                <div class="p-16 text-center text-gray-400">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-2xl text-gray-300"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Products Found</h3>
                    <p class="text-xs text-gray-400 mt-1">We couldn't find any products matching your search term.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Product</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seller</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Price</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Stock</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Created</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-primary">#{{ $product->id }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex items-center justify-center">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <i class="fas fa-image text-gray-300"></i>
                                                @endif
                                            </div>
                                            <span class="text-sm font-bold text-gray-950">{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold">
                                        {{ optional($product->seller)->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-950 font-bold">
                                        {{ number_format($product->price ?? 0, 2) }} PKR
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $product->stock_quantity }} {{ $product->unit_type }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($product->is_approved)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700">
                                                <i class="fas fa-check-circle text-[9px]"></i> Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-50 text-yellow-700">
                                                <i class="fas fa-clock text-[9px]"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ optional($product->created_at)->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end gap-2">
                                            @if(!$product->is_approved)
                                                <form action="{{ route('admin.approveProduct', $product->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition-colors" title="Approve Product">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" onclick="toggleModal('productModal{{ $product->id }}')" title="View Details">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Product Detail Modal -->
                                <div id="productModal{{ $product->id }}" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <!-- Backdrop overlay -->
                                        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="toggleModal('productModal{{ $product->id }}')"></div>

                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                        <!-- Modal Body -->
                                        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                                            <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                                                <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                                    <h3 class="text-lg font-bold text-gray-900" id="modal-title">{{ $product->name }}</h3>
                                                    <button type="button" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors" onclick="toggleModal('productModal{{ $product->id }}')">
                                                        <i class="fas fa-times text-sm"></i>
                                                    </button>
                                                </div>

                                                <div class="mt-6 flex flex-col sm:flex-row gap-6">
                                                    <!-- Modal Product Image -->
                                                    <div class="w-full sm:w-32 h-32 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                        @if($product->image)
                                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                                        @else
                                                            <i class="fas fa-image text-gray-300 text-3xl"></i>
                                                        @endif
                                                    </div>

                                                    <!-- Modal Info -->
                                                    <div class="flex-1 space-y-3">
                                                        <p class="text-sm text-gray-500"><strong class="text-gray-700 font-semibold">Seller:</strong> {{ optional($product->seller)->name ?? 'N/A' }}</p>
                                                        <p class="text-sm text-gray-500"><strong class="text-gray-700 font-semibold">Price:</strong> {{ number_format($product->price ?? 0, 2) }} PKR</p>
                                                        <p class="text-sm text-gray-500"><strong class="text-gray-700 font-semibold">Stock:</strong> {{ $product->stock_quantity }} {{ $product->unit_type }}</p>
                                                        <p class="text-sm text-gray-500"><strong class="text-gray-700 font-semibold">Status:</strong> {{ $product->is_approved ? 'Approved' : 'Pending' }}</p>
                                                    </div>
                                                </div>

                                                <div class="mt-6">
                                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">Description</p>
                                                    <p class="text-sm text-gray-600 bg-gray-50 p-4 rounded-xl border border-gray-100 whitespace-pre-line leading-relaxed">
                                                        {{ $product->description ?: 'No description provided for this listing.' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                                @if(!$product->is_approved)
                                                    <form action="{{ route('admin.approveProduct', $product->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow px-4 py-2 bg-green-600 text-sm font-semibold text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto">
                                                            <i class="fas fa-check mr-1.5 mt-0.5"></i> Approve
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto" onclick="toggleModal('productModal{{ $product->id }}')">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($products, 'hasPages') && $products->hasPages())
                    <div class="p-4 border-t border-gray-50 bg-white">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
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
