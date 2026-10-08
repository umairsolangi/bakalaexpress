@extends('layouts.app')

@section('title', 'Your Cart - Bakala Express')

@section('styles')
    <style>
        .cart-container {
            margin-top: 50px;
            margin-bottom: 50px;
            min-height: calc(100vh - 400px);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 2px;
        }

        .cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .cart-empty i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 1.5rem;
        }

        .cart-empty p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .cart-empty-btn {
            background: linear-gradient(to right, #10b981, #059669);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition-normal);
        }

        .cart-empty-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .cart-table {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: none;
        }

        .cart-table th {
            background: linear-gradient(to right, #10b981, #059669);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            padding: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .cart-table td {
            vertical-align: middle;
            padding: 1rem;
            font-size: 1rem;
            color: var(--text-color);
            border-bottom: 1px solid var(--light-gray);
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-item-name {
            font-weight: 600;
            color: var(--dark-color);
        }

        .cart-item-price,
        .cart-item-quantity,
        .cart-item-total {
            text-align: center;
        }

        .cart-item-price,
        .cart-item-total {
            font-weight: 600;
        }

        .cart-item-total {
            color: #10b981;
        }

        .remove-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius-md);
            font-size: 0.9rem;
            font-weight: 600;
            transition: all var(--transition-normal);
            cursor: pointer;
        }

        .remove-btn:hover {
            background-color: var(--danger-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .cart-summary {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: var(--shadow-md);
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .cart-total-label {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .cart-total-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
        }

        .checkout-btn {
            background: linear-gradient(to right, #10b981, #059669);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius-md);
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            display: block;
            width: 100%;
            text-decoration: none;
            transition: all var(--transition-normal);
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            background: linear-gradient(to right, #059669, #10b981);
            color: white;
        }

        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .continue-shopping:hover {
            color: #10b981;
        }

        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .cart-total-label {
                font-size: 1.1rem;
            }

            .cart-total-value {
                font-size: 1.3rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container cart-container">
        <h1 class="page-title">Your Shopping Cart</h1>

        @if (empty($cart))
            <div class="cart-empty">
                <i class="fas fa-shopping-basket"></i>
                <p>Your cart is empty.</p>
                <a href="{{ route('home') }}" class="cart-empty-btn">
                    <i class="fas fa-arrow-left me-2"></i> Start Shopping
                </a>
            </div>
        @else
            @php
                $sellerNames = collect($cart)->pluck('seller_name')->filter()->unique()->values();
                $hasStockIssue = collect($cart)->contains(fn ($item) => !empty($item['stock_warning']) && str_contains(strtolower($item['stock_warning']), 'out of stock'));
            @endphp
            @if($sellerNames->isNotEmpty())
                <div class="alert alert-info d-flex align-items-start gap-2">
                    <i class="fas fa-store mt-1"></i>
                    <div>
                        <strong>Ordering from {{ $sellerNames->first() }}</strong>
                        <div class="small">One checkout can contain products from one seller only. Clear your cart to switch stores.</div>
                    </div>
                </div>
            @endif

            <div class="card cart-table">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40%">Product</th>
                                <th style="width: 15%">Price</th>
                                <th style="width: 15%">Quantity</th>
                                <th style="width: 15%">Total</th>
                                <th style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach ($cart as $cartKey => $item)
                                <tr>
                                    <td class="cart-item-name">
                                        {{ $item['name'] }}
                                        @if(!empty($item['unit_type']))
                                            <div class="text-muted small mt-1">{{ $item['unit_type'] }}</div>
                                        @endif
                                        @if(!empty($item['seller_name']))
                                            <div class="text-muted small mt-1">Seller: {{ $item['seller_name'] }}</div>
                                        @endif
                                        @if(!empty($item['stock_warning']))
                                            <div class="{{ str_contains(strtolower($item['stock_warning']), 'out of stock') ? 'text-danger' : 'text-warning' }} small mt-1">
                                                <i class="fas fa-triangle-exclamation me-1"></i>{{ $item['stock_warning'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="cart-item-price">{{ number_format($item['price'], 2) }} PKR</td>
                                    <td class="cart-item-quantity">
                                        <form action="{{ route('cart.update') }}" method="POST" class="d-flex justify-content-center align-items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $cartKey }}">
                                            <input type="number" name="quantity" min="1"
                                                max="{{ !empty($item['stock_quantity']) ? max(1, (int) $item['stock_quantity']) : 999 }}"
                                                value="{{ $item['quantity'] }}"
                                                class="form-control form-control-sm text-center"
                                                style="width: 80px;">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Update</button>
                                        </form>
                                    </td>
                                    <td class="cart-item-total">{{ number_format($item['price'] * $item['quantity'], 2) }} PKR</td>
                                    @php $total += $item['price'] * $item['quantity']; @endphp
                                    <td class="text-center">
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="{{ !empty($item['shop_product_id']) ? 'shop_product_id' : 'product_id' }}"
                                                value="{{ !empty($item['shop_product_id']) ? $item['shop_product_id'] : $item['id'] }}">
                                            <button type="submit" class="remove-btn">
                                                <i class="fas fa-trash-alt me-1"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cart-summary">
                <div class="cart-total">
                    <span class="cart-total-label">Total Amount</span>
                    <span class="cart-total-value">{{ number_format($total, 2) }} PKR</span>
                </div>

                <a href="{{ $hasStockIssue ? '#' : route('checkout.show') }}" class="checkout-btn {{ $hasStockIssue ? 'opacity-50 pointer-events-none' : '' }}">
                    <i class="fas fa-check-circle me-2"></i> Proceed to Checkout
                </a>
                @if($hasStockIssue)
                    <div class="text-danger small text-center mt-2">Remove out-of-stock items before checkout.</div>
                @endif
                <a href="{{ route('home') }}" class="continue-shopping mt-3">
                    <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                </a>
            </div>
        @endif
    </div>
@endsection
