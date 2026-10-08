@extends('layouts.app')

@section('title', 'Fastest Grocery Delivery - Bakala Express')

@section('styles')
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        .font-display {
            font-family: 'Poppins', sans-serif;
        }

        .filter-card {
            backdrop-filter: blur(10px);
        }
    </style>
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gray-50 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-light/30 to-transparent"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 animate__animated animate__fadeInLeft">
                    <h1 class="text-5xl lg:text-7xl font-bold font-display text-gray-900 leading-tight">
                        Your Daily Needs <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark">Delivered
                            in Minutes</span>
                    </h1>
                    <p class="text-xl text-gray-600 max-w-lg leading-relaxed">
                        Shop for fresh meat, vegetables, fruits, and daily essentials from your trusted neighborhood
                        Bakalas. The smartest way to shop local.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#products"
                            class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white transition-all duration-200 bg-primary border border-transparent rounded-full hover:bg-primary-dark shadow-lg hover:shadow-primary/40 transform hover:-translate-y-1">
                            Order Now <i class="fas fa-shopping-bag ml-2"></i>
                        </a>
                        <a href="#sellers"
                            class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:text-primary shadow-sm hover:shadow-md">
                            Find Stores
                        </a>
                    </div>

                    <div class="flex items-center gap-8 pt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-primary">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Under 30 Mins</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="fas fa-check-shield"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Secure Payments</span>
                        </div>
                    </div>
                </div>
                <div class="relative animate__animated animate__fadeInRight lg:block hidden">
                    <div class="absolute -top-10 -right-10 w-72 h-72 bg-accent/20 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-10 -left-10 w-72 h-72 bg-primary/20 rounded-full blur-3xl"></div>
                    <img id="heroImage"
                        src="https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=1974&auto=format&fit=crop"
                        alt="Grocery Delivery"
                        class="relative rounded-3xl shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500 border-8 border-white transition-opacity duration-300 w-full h-[500px] object-cover">
                    <!-- Floating Card -->
                    <div class="absolute -bottom-8 -left-8 bg-white p-4 rounded-xl shadow-xl border border-gray-100 animate-bounce"
                        style="animation-duration: 3s;">
                        <div class="flex items-center gap-3">
                            <div class="bg-green-100 p-2 rounded-lg text-green-600">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Daily Status</p>
                                <p class="font-bold text-gray-800">Fresh Stock</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-primary font-semibold tracking-wider uppercase text-sm">Categories</span>
                <h2 class="mt-2 text-3xl font-bold font-display text-gray-900 sm:text-4xl">Shop by Category</h2>
                <div class="mt-4 h-1 w-20 bg-primary mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <!-- Category Items -->
                @foreach($categories as $cat)
                    <a href="{{ route('home', ['category' => $cat->id]) }}#sellers"
                        class="group relative overflow-hidden bg-gray-50 rounded-2xl p-6 text-center hover:bg-white hover:shadow-xl transition-all duration-300 border border-transparent hover:border-gray-100">
                        <div
                            class="w-16 h-16 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300 text-primary">
                            <i class="fas fa-box text-2xl" style="color: var(--primary);"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary transition-colors">{{ $cat->name }}
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Top Shop Keepers Section -->
    <section class="py-12 bg-gray-50 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="text-xl font-bold text-gray-900 mb-8 text-center">Top Shop Keepers</h3>
            <div class="swiper brandSwiper">
                <div class="swiper-wrapper items-center pb-8">
                    @php
                        $shopToppers = [
                            ['name' => 'Kamran Meat', 'img' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?q=80&w=200&auto=format&fit=crop'],
                            ['name' => 'Siddique General Store', 'img' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?q=80&w=200&auto=format&fit=crop'],
                            ['name' => 'Zahid Samosa', 'img' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=200&auto=format&fit=crop'],
                            ['name' => 'Rehan Roll Point', 'img' => 'https://images.unsplash.com/photo-1561758033-d8f587294801?q=80&w=200&auto=format&fit=crop'],
                            ['name' => 'Mazhar Dairy', 'img' => 'https://images.unsplash.com/photo-1628102491629-778571d893a3?q=80&w=200&auto=format&fit=crop'],
                            ['name' => 'Bismillah Store', 'img' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=200&auto=format&fit=crop'],
                            ['name' => 'Al-Madina Sweets', 'img' => 'https://images.unsplash.com/photo-1576189953839-a99f66089d38?q=80&w=200&auto=format&fit=crop']
                        ];
                    @endphp
                    @foreach($shopToppers as $shop)
                        <div class="swiper-slide flex justify-center">
                            <div class="flex flex-col items-center group cursor-pointer">
                                <div
                                    class="w-24 h-24 rounded-full p-1 bg-gradient-to-tr from-primary to-accent mb-3 shadow-md group-hover:scale-110 transition-transform duration-300">
                                    <img src="{{ $shop['img'] }}"
                                        class="w-full h-full rounded-full object-cover border-2 border-white"
                                        alt="{{ $shop['name'] }}">
                                </div>
                                <h4
                                    class="font-bold text-gray-800 text-sm p-1 rounded-full group-hover:text-primary transition-colors text-center">
                                    {{ $shop['name'] }}</h4>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-16 bg-white" id="products">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4">
                <div>
                    <span class="text-primary font-semibold tracking-wider uppercase text-sm">New Arrivals</span>
                    <h2 class="mt-2 text-3xl font-bold font-display text-gray-900">Fresh from Bakala</h2>
                </div>
                <div class="relative w-full md:w-auto">
                    <input type="text" id="serviceSearch" onkeyup="searchServices()" placeholder="Search products..."
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent w-full md:w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <div id="searchResults"
                        class="absolute z-50 w-full bg-white shadow-xl rounded-lg mt-1 hidden max-h-60 overflow-y-auto border border-gray-100">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @php
                    $newArrivals = [
                        [
                            'id' => 1,
                            'name' => 'Olpers Milk 1L',
                            'price' => 280,
                            'unit' => 'Carton',
                            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=600&auto=format&fit=crop', // Milk carton
                            'is_new' => true
                        ],
                        [
                            'id' => 2,
                            'name' => 'Nestle Milk Pak 1L',
                            'price' => 270,
                            'unit' => 'Carton',
                            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=600&auto=format&fit=crop', // Milk carton
                            'is_new' => true
                        ],
                        [
                            'id' => 3,
                            'name' => 'Dawn Bread Large',
                            'price' => 150,
                            'unit' => 'Packet',
                            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=600&auto=format&fit=crop', // Bread
                            'is_new' => true
                        ],
                        [
                            'id' => 4,
                            'name' => 'Farm Fresh Eggs',
                            'price' => 400,
                            'unit' => 'Dozen',
                            'image' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?q=80&w=600&auto=format&fit=crop', // Eggs
                            'is_new' => true
                        ],
                        [
                            'id' => 5,
                            'name' => 'Nestle Yogurt',
                            'price' => 120,
                            'unit' => 'Cup',
                            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=600&auto=format&fit=crop', // Milk carton
                            'is_new' => true
                        ],
                        [
                            'id' => 6,
                            'name' => 'Lays Masala',
                            'price' => 80,
                            'unit' => 'Packet',
                            'image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?q=80&w=600&auto=format&fit=crop', // Chips
                            'is_new' => true
                        ],
                        [
                            'id' => 7,
                            'name' => 'Bananas (Dozen)',
                            'price' => 200,
                            'unit' => 'Dozen',
                            'image' => 'https://images.unsplash.com/photo-1603833665858-e61d17a86224?q=80&w=600&auto=format&fit=crop', // Bananas
                            'is_new' => true
                        ],
                        [
                            'id' => 8,
                            'name' => 'Coca Cola 1.5L',
                            'price' => 180,
                            'unit' => 'Bottle',
                            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=600&auto=format&fit=crop', // Milk carton
                            'is_new' => true
                        ]
                    ];
                @endphp

                @foreach($newArrivals as $product)
                    <div
                        class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col h-full relative">
                        <!-- Badges -->
                        @if($product['is_new'])
                            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                                <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-sm">NEW</span>
                            </div>
                        @endif

                        <!-- Image -->
                        <div class="relative h-64 overflow-hidden bg-gray-100">
                            <img src="{{ $product['image'] }}"
                                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
                                alt="{{ $product['name'] }}">

                            <!-- Quick Action Overlay -->
                            <div
                                class="absolute inset-x-0 bottom-0 p-4 bg-white/90 backdrop-blur-sm transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 flex justify-center">
                                <a href="#" onclick="event.preventDefault(); addToCart({{ $product['id'] }})"
                                    class="w-full py-2 bg-primary hover:bg-primary-dark text-white text-center rounded-lg font-semibold shadow-md transition-colors">
                                    Add to Cart
                                </a>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex-grow flex flex-col">
                            <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide">{{ $product['unit'] }}</div>
                            <h3
                                class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                                <a href="#">{{ $product['name'] }}</a>
                            </h3>

                            <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 line-through">PKR
                                        {{ number_format($product['price'] * 1.1) }}</span>
                                    <span class="text-xl font-bold text-primary">PKR
                                        {{ number_format($product['price']) }}</span>
                                </div>
                                <button
                                    class="w-10 h-10 rounded-full bg-gray-50 hover:bg-primary hover:text-white flex items-center justify-center transition-colors text-gray-600">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('order.all') }}"
                    class="inline-flex items-center justify-center px-8 py-3 bg-white border border-gray-200 text-gray-700 font-medium rounded-full hover:bg-gray-50 transition-colors shadow-sm">
                    View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Sellers/Partners Section (Migrated) -->
    <section class="py-16 bg-gray-50" id="sellers">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-primary font-semibold tracking-wider uppercase text-sm">Our PArtners</span>
                <h2 class="text-3xl font-bold font-display text-gray-900">Neighborhood Bakalas</h2>
            </div>

            <div class="bg-white/90 filter-card rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6 mb-8">
                <form method="GET" action="{{ route('home') }}#sellers" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select
                            id="categoryFilter"
                            name="category"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sectorFilter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Sector</label>
                        <select
                            id="sectorFilter"
                            name="sector"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                            <option value="">All Sectors</option>
                            @foreach($sectorOptions as $sectorOption)
                                <option value="{{ $sectorOption }}" {{ request('sector') === $sectorOption ? 'selected' : '' }}>
                                    {{ $sectorOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="nearAreaFilter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Near Area</label>
                        <select
                            id="nearAreaFilter"
                            name="near_area"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                            <option value="">All Near Areas</option>
                            @foreach($nearAreaOptions as $nearAreaOption)
                                <option value="{{ $nearAreaOption }}" {{ request('near_area') === $nearAreaOption ? 'selected' : '' }}>
                                    {{ $nearAreaOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 flex flex-col sm:flex-row gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition-colors"
                        >
                            Apply Filters
                        </button>
                        <a
                            href="{{ route('home') }}#sellers"
                            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                        >
                            Clear Filters
                        </a>
                    </div>
                </form>

                @if(request('sector') || request('near_area') || request('category'))
                    <div class="mt-4 text-sm text-gray-600">
                        Showing sellers
                        @if(request('category'))
                            in category <span class="font-semibold text-gray-900">{{ $categories->firstWhere('id', request('category'))?->name }}</span>
                        @endif
                        @if(request('sector'))
                            in sector <span class="font-semibold text-gray-900">{{ request('sector') }}</span>
                        @endif
                        @if(request('near_area'))
                            near <span class="font-semibold text-gray-900">{{ request('near_area') }}</span>
                        @endif
                    </div>
                @endif
            </div>

            @auth
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($sellers as $seller)
                        @php
                            $catDetails = $seller->getCategoryProfileDetails();
                            $isOpen = $seller->isAcceptingOrders();
                        @endphp
                        <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all border border-gray-100 text-center group flex flex-col justify-between">
                            <div>
                                <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gray-50 overflow-hidden border-2 border-white shadow-md relative">
                                    <img src="{{ $seller->display_profile_image }}"
                                        alt="{{ $seller->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-primary/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="mb-2">
                                    <span class="px-2.5 py-0.5 rounded-full bg-primary-light text-primary text-[11px] font-semibold inline-flex items-center gap-1">
                                        <i class="fas {{ $catDetails['icon'] }} text-xs"></i>
                                        {{ $seller->catalogCategory?->name ?? 'Store' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-gray-900 text-lg mb-1 line-clamp-1">{{ $seller->name }}</h3>
                                <div class="flex flex-wrap justify-center gap-1.5 mb-3">
                                    @if($isOpen)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Open</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-xs font-semibold">Closed</span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">Verified</span>
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">30-45 min</span>
                                </div>
                                <p class="text-gray-500 text-xs mb-2 flex items-center justify-center gap-1">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    {{ $seller->area }}, {{ $seller->sector ? 'Sector ' . $seller->sector : 'Karachi' }}
                                </p>
                                <p class="text-gray-500 text-xs mb-3">
                                    {{ $seller->catalog_products_count ?? 0 }} products ·
                                    <i class="fas fa-star text-amber-400"></i>
                                    {{ number_format((float) ($seller->approved_feedbacks_avg_rating ?? 0), 1) }}
                                    ({{ $seller->feedbacks_count ?? 0 }})
                                </p>
                            </div>

                            <div>
                                @php
                                    $hoursFormatted = date('h:i A', strtotime($seller->opens_at ?? '07:00')) . ' – ' . date('h:i A', strtotime($seller->closes_at ?? '23:00'));
                                @endphp
                                @if($isOpen)
                                    <a href="{{ route('sellers.services', $seller->id) }}"
                                        class="block w-full py-2 bg-white border border-primary text-primary hover:bg-primary hover:text-white rounded-xl font-semibold text-sm transition-colors shadow-sm mb-2">
                                        Visit Store
                                    </a>
                                @else
                                    <button type="button"
                                        onclick="openClosedStoreModal('{{ addslashes($seller->name) }}', '{{ $hoursFormatted }}')"
                                        class="block w-full py-2 bg-gray-50 border border-gray-200 text-gray-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 rounded-xl font-semibold text-sm transition-colors shadow-sm mb-2 flex items-center justify-center gap-1.5">
                                        <i class="fas fa-lock text-xs text-rose-500"></i> Store Closed
                                    </button>
                                @endif
                                <form action="{{ route('favorites.sellers.toggle', $seller) }}" method="POST">
                                    @csrf
                                    @php $sellerFavorited = auth()->user()?->favorites()->where('seller_id', $seller->id)->exists(); @endphp
                                    <button type="submit"
                                        class="block w-full py-1.5 border border-gray-200 rounded-xl font-medium text-xs transition-colors {{ $sellerFavorited ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                                        <i class="{{ $sellerFavorited ? 'fas text-rose-500' : 'far' }} fa-heart mr-1"></i>
                                        {{ $sellerFavorited ? 'Favorited' : 'Favorite' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($sellers->hasPages())
                    <div class="mt-8 flex justify-center">
                        {{ $sellers->links() }}
                    </div>
                @endif
                @if($sellers->isEmpty())
                    <div class="text-center py-12 bg-white rounded-2xl shadow-sm border border-gray-100 max-w-2xl mx-auto">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            <i class="fas fa-store-slash text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No sellers match these filters</h3>
                        <p class="text-gray-500 mb-6">Try another sector or near area.</p>
                        <a href="{{ route('home') }}#sellers"
                            class="inline-flex items-center justify-center px-8 py-3 bg-primary text-white font-medium rounded-full hover:bg-primary-dark transition-colors shadow-lg hover:shadow-primary/40">
                            Reset Filters
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-12 bg-white rounded-2xl shadow-sm border border-gray-100 max-w-2xl mx-auto">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Login to View Partners</h3>
                    <p class="text-gray-500 mb-6">See which shops are delivering in your area.</p>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-8 py-3 bg-primary text-white font-medium rounded-full hover:bg-primary-dark transition-colors shadow-lg hover:shadow-primary/40">
                        Sign In Now
                    </a>
                </div>
            @endauth
        </div>
    </section>

    <!-- Feature Section (Why Us) -->
    <section class="py-20 bg-primary-dark relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h2 class="text-3xl font-bold font-display text-white sm:text-4xl mb-16">Why Choose Bakala Express?</h2>

            <div class="grid md:grid-cols-3 gap-12">
                <div
                    class="p-8 rounded-3xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-colors">
                    <div
                        class="w-16 h-16 mx-auto mb-6 bg-accent rounded-2xl flex items-center justify-center shadow-lg transform rotate-3 hover:rotate-6 transition-transform">
                        <i class="fas fa-shipping-fast text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Super Fast Delivery</h3>
                    <p class="text-gray-300 leading-relaxed">
                        We deliver from your nearest Bakala, ensuring your groceries arrive in minutes, not hours.
                    </p>
                </div>

                <div
                    class="p-8 rounded-3xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-colors">
                    <div
                        class="w-16 h-16 mx-auto mb-6 bg-primary rounded-2xl flex items-center justify-center shadow-lg transform -rotate-3 hover:-rotate-6 transition-transform">
                        <i class="fas fa-carrot text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Freshness Guaranteed</h3>
                    <p class="text-gray-300 leading-relaxed">
                        We carefully select our partners to ensure you only get the freshest produce and quality items.
                    </p>
                </div>

                <div
                    class="p-8 rounded-3xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-colors">
                    <div
                        class="w-16 h-16 mx-auto mb-6 bg-blue-500 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3 hover:rotate-6 transition-transform">
                        <i class="fas fa-headset text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">24/7 Support</h3>
                    <p class="text-gray-300 leading-relaxed">
                        Our dedicated support team is always here to help you with your orders and queries.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section (Migrated) -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold font-display text-gray-900">Customer Love</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($feedbacks->take(3) as $feedback)
                    <div class="bg-gray-50 rounded-2xl p-8 relative">
                        <i class="fas fa-quote-left text-4xl text-primary/10 absolute top-6 left-6"></i>
                        <p class="text-gray-600 mb-6 relative z-10 italic">"{{ $feedback->feedback }}"</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">
                                {{ substr($feedback->user ? $feedback->user->name : 'A', 0, 1) }}
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-sm">
                                    {{ $feedback->user ? $feedback->user->name : 'Anonymous' }}
                                </h5>
                                <p class="text-xs text-gray-500">Verified Customer</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($feedbacks->isEmpty())
                <div class="text-center text-gray-500 py-8">
                    <i class="far fa-smile text-4xl mb-3 block"></i>
                    <p>Be the first to leave a review!</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Partner CTA -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl shadow-2xl overflow-hidden relative">
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-primary/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-accent/20 rounded-full blur-3xl"></div>

                <div
                    class="relative z-10 px-6 py-16 md:px-12 md:py-20 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-10">
                    <div class="max-w-2xl">
                        <h2 class="text-3xl font-bold text-white sm:text-4xl mb-6">Own a Bakala? Join Us!</h2>
                        <p class="text-gray-300 text-lg mb-8">
                            Expand your business by partnering with us. Reach more customers in your neighborhood and grow
                            your sales.
                        </p>
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-gray-900 transition-all duration-200 bg-white border border-transparent rounded-full hover:bg-gray-100 shadow-lg hover:shadow-xl">
                            Register Your Store
                        </a>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-store text-9xl text-white/10"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Hero Image Carousel
        document.addEventListener('DOMContentLoaded', function () {
            const heroImage = document.getElementById('heroImage');
            const images = [
                'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=1974&auto=format&fit=crop', // Groceries (Current)
                'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=2070&auto=format&fit=crop', // Fruits
                'https://images.unsplash.com/photo-1597362925123-77861d3fbac7?q=80&w=2070&auto=format&fit=crop', // Veggies
                'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?q=80&w=2070&auto=format&fit=crop', // Meat
                'https://images.unsplash.com/photo-1628102491629-778571d893a3?q=80&w=2080&auto=format&fit=crop', // Dairy
                'https://images.unsplash.com/photo-1588611910609-8800bd8d39e9?q=80&w=2070&auto=format&fit=crop', // General items
                'https://images.unsplash.com/photo-1550989460-0adf9ea622e2?q=80&w=1974&auto=format&fit=crop', // Supermarket aisle
                'https://images.unsplash.com/photo-1576189953839-a99f66089d38?q=80&w=2070&auto=format&fit=crop', // Bakery
                'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?q=80&w=2073&auto=format&fit=crop', // Sandwich
                'https://images.unsplash.com/photo-1506484381205-f7945653044d?q=80&w=2070&auto=format&fit=crop'  // Market
            ];

            let currentIndex = 0;

            setInterval(() => {
                // Fade out
                heroImage.style.opacity = '0';

                setTimeout(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    heroImage.src = images[currentIndex];

                    // Fade in
                    heroImage.onload = () => {
                        heroImage.style.opacity = '1';
                    };
                }, 300); // Wait for fade out

            }, 2000); // Change every 2 seconds
        });
    </script>
    <script>
        // Start Swiper
        var swiper = new Swiper(".brandSwiper", {
            slidesPerView: 2,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 5,
                    spaceBetween: 40,
                },
            },
        });

        // Search Functionality
        function searchServices() {
            let searchTerm = document.getElementById('serviceSearch').value;
            let resultContainer = document.getElementById('searchResults');

            if (searchTerm.length >= 3) {
                resultContainer.classList.remove('hidden');

                // Demo purposes as we cannot hit backend in this view
                // In production, fetch logic would go here

                fetch(`/search-products?q=${searchTerm}`)
                    .then(response => response.json())
                    .then(data => {
                        let results = data.products || [];
                        resultContainer.innerHTML = '';

                        if (results.length > 0) {
                            results.forEach(product => {
                                let div = document.createElement('div');
                                div.className = 'p-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 cursor-pointer transition-colors';
                                div.innerHTML = `
                                        <div class="font-medium text-gray-900">${product.name}</div>
                                        <div class="text-xs text-gray-500 truncate">${product.description || ''}</div>
                                        <div class="text-xs text-primary mt-1">${product.seller_name || ''} · PKR ${Number(product.price || 0).toLocaleString()}</div>
                                        <div class="text-xs ${product.is_available ? 'text-green-600' : 'text-red-600'} mt-1">${product.is_available ? 'In stock' : 'Out of stock'}</div>
                                     `;
                                div.onclick = function () {
                                    window.location.href = `/sellers/${product.seller_id}/products/${product.shop_product_id}`;
                                };
                                resultContainer.appendChild(div);
                            });
                        } else {
                            resultContainer.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">No products found</div>';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        resultContainer.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">No products found</div>';
                    });

            } else {
                resultContainer.classList.add('hidden');
                resultContainer.innerHTML = '';
            }
        }
    </script>
@endsection
