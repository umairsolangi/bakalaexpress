@extends('layouts.app')

@section('title', $seller->name . ' - Bakala Express')

@section('content')
@php
    $catDetails = $seller->getCategoryProfileDetails();
    $sellerImg = $seller->display_profile_image;
@endphp

<div class="bg-gray-50 min-h-screen pb-16">
    <!-- Seller Hero Banner with Category Accent Gradient -->
    <div class="relative bg-gradient-to-r {{ $catDetails['gradient'] }} text-white overflow-hidden shadow-xl">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm z-0"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 relative z-10">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Seller Profile Image -->
                <div class="relative flex-shrink-0">
                    <div class="w-32 h-32 md:w-36 md:h-36 rounded-2xl bg-white p-1.5 shadow-2xl overflow-hidden border-4 border-white/20">
                        <img src="{{ $sellerImg }}" alt="{{ $seller->name }}" class="w-full h-full object-cover rounded-xl">
                    </div>
                    @if($seller->isAcceptingOrders())
                        <span class="absolute bottom-2 right-2 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full shadow" title="Open Now"></span>
                    @else
                        <span class="absolute bottom-2 right-2 w-5 h-5 bg-rose-500 border-2 border-white rounded-full shadow" title="Closed"></span>
                    @endif
                </div>

                <!-- Seller Details -->
                <div class="flex-1 text-center md:text-left space-y-3">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                        @if($seller->catalogCategory)
                            <span class="px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 text-xs font-medium rounded-full text-amber-300">
                                <i class="fas {{ $catDetails['icon'] }} text-amber-400 mr-1"></i> {{ $seller->catalogCategory->name }}
                            </span>
                        @endif
                        @if((int) ($seller->accountIsApproved ?? 0) === 1)
                            <span class="px-3 py-1 bg-blue-500/20 backdrop-blur-md border border-blue-400/30 text-xs font-medium rounded-full text-blue-300">
                                <i class="fas fa-check-circle text-blue-400 mr-1"></i> Verified Partner
                            </span>
                        @endif
                        @if($seller->isAcceptingOrders())
                            <span class="px-3 py-1 bg-emerald-500/20 backdrop-blur-md border border-emerald-400/30 text-xs font-semibold rounded-full text-emerald-300">
                                <i class="fas fa-door-open mr-1"></i> Open Now
                            </span>
                        @else
                            <span class="px-3 py-1 bg-rose-500/20 backdrop-blur-md border border-rose-400/30 text-xs font-semibold rounded-full text-rose-300">
                                <i class="fas fa-door-closed mr-1"></i> Currently Closed
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-5xl font-bold font-display tracking-tight text-white">
                        {{ $seller->name }}
                    </h1>

                    <p class="text-amber-200 text-xs md:text-sm font-medium tracking-wide">
                        <i class="fas {{ $catDetails['icon'] }} mr-1"></i> {{ $catDetails['tagline'] }}
                    </p>

                    <p class="text-gray-300 text-sm md:text-base max-w-2xl flex items-center justify-center md:justify-start gap-2">
                        <i class="fas fa-map-marker-alt text-primary-light"></i>
                        <span>{{ $seller->full_address ?? ($seller->area . ', ' . ($seller->sector ? 'Sector ' . $seller->sector . ', ' : '') . $seller->city) }}</span>
                    </p>

                    @if(is_array($seller->near_areas) && count($seller->near_areas) > 0)
                        <p class="text-gray-400 text-xs flex items-center justify-center md:justify-start gap-2">
                            <i class="fas fa-compass text-accent"></i>
                            <span>Near: {{ implode(', ', $seller->near_areas) }}</span>
                        </p>
                    @endif

                    <!-- Stats Row -->
                    <div class="pt-2 flex flex-wrap items-center justify-center md:justify-start gap-6 text-sm">
                        <div class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-lg backdrop-blur-md">
                            <i class="fas fa-star text-amber-400"></i>
                            <span class="font-bold text-white">{{ number_format((float) $feedbacks->avg('rating'), 1) }}</span>
                            <span class="text-gray-300 text-xs">({{ $feedbacks->count() }} {{ Str::plural('review', $feedbacks->count()) }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-lg backdrop-blur-md text-gray-200">
                            <i class="fas fa-box text-primary-light"></i>
                            <span>{{ $products->count() }} {{ Str::plural('Product', $products->count()) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-lg backdrop-blur-md text-gray-200">
                            <i class="fas fa-clock text-blue-300"></i>
                            <span>{{ date('h:i A', strtotime($seller->opens_at ?? '07:00')) }} – {{ date('h:i A', strtotime($seller->closes_at ?? '23:00')) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Favorite Button Action -->
                @auth
                    <div class="flex-shrink-0 pt-2 md:pt-0">
                        <form action="{{ route('favorites.sellers.toggle', $seller) }}" method="POST">
                            @csrf
                            @php $sellerFavorited = auth()->user()?->favorites()->where('seller_id', $seller->id)->exists(); @endphp
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-medium transition-all shadow-lg backdrop-blur-md border {{ $sellerFavorited ? 'bg-rose-600 text-white border-rose-500 hover:bg-rose-700' : 'bg-white/10 text-white border-white/20 hover:bg-white/20' }}">
                                <i class="{{ $sellerFavorited ? 'fas text-white' : 'far' }} fa-heart text-lg"></i>
                                <span>{{ $sellerFavorited ? 'Favorited' : 'Favorite Store' }}</span>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Store Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Session Alert -->
        @if (session('success'))
            <div class="mb-8 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate__animated animate__fadeIn">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-8 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm animate__animated animate__fadeIn">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 text-lg"></i>
                    <span class="font-medium text-sm">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold font-display text-gray-900 flex items-center gap-2">
                        <i class="fas {{ $catDetails['icon'] }} text-primary"></i>
                        <span>Available Products</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Explore {{ $seller->catalogCategory?->name ?? 'Category' }} inventory offered by {{ $seller->name }}</p>
                </div>
                <div class="relative w-full md:w-80">
                    <input type="text" id="shopProductSearch" onkeyup="filterShopProducts()" placeholder="Search in this shop..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        @if ($products->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center my-8">
                <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center text-primary mx-auto mb-4 text-3xl">
                    <i class="fas {{ $catDetails['icon'] }}"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">No Products Found</h3>
                <p class="text-gray-500 text-sm mt-1 max-w-md mx-auto">This shop has not added active products yet. Check back soon!</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-6 px-6 py-2.5 bg-primary text-white rounded-full font-semibold text-sm hover:bg-primary-dark transition-all shadow-md">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="productGrid">
                @foreach ($products as $product)
                    @php
                        $stock = (int) $product->pivot->stock_quantity;
                        $isInStock = $stock > 0;
                        $isLowStock = $stock > 0 && $stock <= 5;
                        $listingId = $product->pivot->id;
                    @endphp
                    <div class="shop-product-card group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col h-full relative"
                         data-name="{{ strtolower($product->name) }}" data-description="{{ strtolower($product->description) }}">
                        
                        <!-- Badges -->
                        <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5">
                            @if(!$isInStock)
                                <span class="px-2.5 py-1 bg-rose-500 text-white text-[10px] font-bold rounded-full shadow-sm tracking-wide uppercase">Out of Stock</span>
                            @elseif($isLowStock)
                                <span class="px-2.5 py-1 bg-amber-500 text-white text-[10px] font-bold rounded-full shadow-sm tracking-wide uppercase">Only {{ $stock }} Left</span>
                            @else
                                <span class="px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full shadow-sm tracking-wide uppercase">In Stock</span>
                            @endif
                        </div>

                        <!-- Wishlist Toggle -->
                        @auth
                            @php $productFavorited = auth()->user()?->favorites()->where('shop_product_id', $listingId)->exists(); @endphp
                            <form action="{{ route('favorites.products.toggle', $listingId) }}" method="POST" class="absolute top-3 right-3 z-10">
                                @csrf
                                <button type="submit" class="w-9 h-9 rounded-full bg-white/90 backdrop-blur-md shadow-md hover:bg-white flex items-center justify-center transition-all text-gray-600 hover:text-rose-500">
                                    <i class="{{ $productFavorited ? 'fas text-rose-500' : 'far' }} fa-heart text-sm"></i>
                                </button>
                            </form>
                        @endauth

                        <!-- Image Section -->
                        <div class="relative h-48 overflow-hidden bg-gray-50 flex items-center justify-center p-3">
                            <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover rounded-xl transform group-hover:scale-105 transition-transform duration-500">
                        </div>

                        <!-- Product Information -->
                        <div class="p-5 flex-grow flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-primary uppercase tracking-wider block mb-1">
                                    {{ $product->unit_type ?? 'Piece' }}
                                </span>
                                <h3 class="font-bold text-gray-900 text-base mb-1 line-clamp-1 group-hover:text-primary transition-colors">
                                    <a href="{{ route('catalog.product.show', [$seller->id, $listingId]) }}">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-4">
                                    {{ $product->description ?? 'Fresh product available directly from store.' }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-gray-100">
                                <div class="flex items-baseline justify-between mb-3">
                                    <span class="text-xs text-gray-400 font-medium">Price</span>
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ number_format($product->effective_price, 0) }} <span class="text-xs font-semibold text-gray-500">PKR</span>
                                    </span>
                                </div>

                                <form action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="shop_product_id" value="{{ $listingId }}">
                                    <button type="submit" 
                                        class="w-full py-2.5 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all shadow-sm {{ $isInStock ? 'bg-primary hover:bg-primary-dark text-white shadow-primary/20 hover:shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}"
                                        {{ !$isInStock ? 'disabled' : '' }}>
                                        <i class="fas fa-shopping-cart text-xs"></i>
                                        <span>{{ $isInStock ? 'Add to Cart' : 'Out of Stock' }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Customer Feedback & Reviews Section -->
        <div class="mt-16 bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-8 pb-6 border-b border-gray-100">
                <div>
                    <h2 class="text-2xl font-bold font-display text-gray-900">Customer Ratings & Reviews</h2>
                    <p class="text-sm text-gray-500 mt-1">Verified buyer feedback for {{ $seller->name }}</p>
                </div>
                <div class="flex items-center gap-4 bg-amber-50 border border-amber-200/60 px-5 py-3 rounded-2xl">
                    <div class="text-3xl font-extrabold text-amber-600">
                        {{ number_format((float) $feedbacks->avg('rating'), 1) }}
                    </div>
                    <div>
                        <div class="flex text-amber-400 text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= round((float) $feedbacks->avg('rating')) ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                        </div>
                        <div class="text-xs font-medium text-amber-800 mt-0.5">{{ $feedbacks->count() }} {{ Str::plural('Review', $feedbacks->count()) }}</div>
                    </div>
                </div>
            </div>

            @if($feedbacks->isEmpty())
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mx-auto mb-3 text-2xl">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h4 class="font-bold text-gray-800">No Reviews Yet</h4>
                    <p class="text-gray-500 text-xs mt-1">Be the first customer to leave feedback after completing an order!</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($feedbacks as $feedback)
                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex flex-col justify-between hover:bg-white hover:shadow-md transition-all">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex text-amber-400 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= (int) $feedback->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-medium">
                                        {{ $feedback->order ? $feedback->order->created_at->format('M d, Y') : 'Recent' }}
                                    </span>
                                </div>
                                <p class="text-gray-700 text-sm italic leading-relaxed mb-4">
                                    "{{ $feedback->feedback }}"
                                </p>
                            </div>
                            <div class="flex items-center gap-3 pt-3 border-t border-gray-200/60">
                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($feedback->user ? $feedback->user->name : 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 text-xs">{{ $feedback->user ? $feedback->user->name : 'Verified Customer' }}</div>
                                    <div class="text-[10px] text-gray-400">Order #{{ $feedback->order_id }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function filterShopProducts() {
    const query = document.getElementById('shopProductSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.shop-product-card');

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const desc = card.getAttribute('data-description');

        if (name.includes(query) || desc.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
