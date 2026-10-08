@extends('layouts.app')

@section('content')
    <div class="container py-8">
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-body p-0">
                        <img src="{{ $product->display_image_url }}" class="img-fluid rounded w-100"
                            alt="{{ $product->name }}" style="max-height: 420px; object-fit: cover;">
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-md-6">
                <h1 class="display-5 fw-bold text-gray-800">{{ $product->name }}</h1>
                <p class="text-muted mb-4">Sold by <a href="{{ route('sellers.services', $seller->id) }}"
                        class="text-primary fw-bold">{{ $seller->name }}</a></p>

                <div class="d-flex align-items-center mb-4">
                    <h2 class="text-success fw-bold me-3 mb-0">{{ number_format($product->effective_price, 2) }} PKR</h2>
                    <span class="badge bg-secondary">{{ $product->unit_type }}</span>
                </div>

                @php $stockQuantity = isset($listing) ? (int) $listing->stock_quantity : (int) ($product->stock_quantity ?? 0); @endphp
                @if($stockQuantity > 0)
                    <p class="{{ $stockQuantity <= 5 ? 'text-warning' : 'text-success' }} mb-4">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ $stockQuantity <= 5 ? 'Only ' . $stockQuantity . ' left' : 'Available for delivery' }}
                    </p>
                @else
                    <p class="text-danger mb-4"><i class="fas fa-triangle-exclamation me-1"></i> Out of stock</p>
                @endif

                <p class="lead text-gray-700 mb-5">{{ $product->description }}</p>

                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="shop_product_id" value="{{ $product->pivot->id }}">
                    <button type="submit"
                        {{ $stockQuantity < 1 ? 'disabled' : '' }}
                        class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow-sm transition hover:scale-105">
                        <i class="fas fa-cart-plus me-2"></i> {{ $stockQuantity > 0 ? 'Add to Cart' : 'Unavailable' }}
                    </button>
                </form>
                @auth
                    <form action="{{ route('favorites.products.toggle', $product->pivot->id) }}" method="POST" class="mt-3">
                        @csrf
                        @php $productFavorited = auth()->user()?->favorites()->where('shop_product_id', $product->pivot->id)->exists(); @endphp
                        <button type="submit" class="btn btn-outline-secondary btn-lg w-100 rounded-pill">
                            <i class="{{ $productFavorited ? 'fas text-danger' : 'far' }} fa-heart me-2"></i>
                            {{ $productFavorited ? 'Saved to favorites' : 'Save to favorites' }}
                        </button>
                    </form>
                @endauth

                <div class="mt-4 border-top pt-4">
                    <h5 class="fw-bold mb-3">Seller Details</h5>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i> {{ $seller->city }},
                        {{ $seller->area }}</p>
                    <p><i class="fas fa-store me-2 text-muted"></i> {{ $seller->catalogCategory?->name ?? 'Catalog store' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
