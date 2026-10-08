@extends('layouts.app')

@section('title', 'Checkout - Bakala Express')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/styleHome.css') }}">
    <style>
        .checkout-container {
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
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
            border-radius: 2px;
        }

        .checkout-form {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            height: 50px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-md);
            padding: 10px 15px;
            font-size: 16px;
            transition: all var(--transition-normal);
        }

        .form-control:focus {
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            border-color: var(--primary-color);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .payment-methods {
            margin-top: 20px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .payment-option input[type="radio"] {
            margin-right: 10px;
        }

        .payment-option label {
            font-weight: 500;
            color: var(--dark-color);
            cursor: pointer;
        }

        .payment-services {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .payment-card {
            flex: 1;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-md);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-normal);
        }

        .payment-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .payment-card.active {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .payment-card-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .payment-card-account {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .order-summary {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            padding: 30px;
        }

        .order-item {
            margin-bottom: 15px;
        }

        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .order-row:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 500;
            color: var(--dark-color);
        }

        .item-price {
            font-weight: 600;
            color: var(--primary-color);
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            margin-top: 15px;
            border-top: 2px solid var(--light-gray);
        }

        .total-label {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 1.1rem;
        }

        .total-value {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .place-order-btn {
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: var(--border-radius-md);
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all var(--transition-normal);
        }

        .place-order-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            background: linear-gradient(to right, var(--primary-dark), var(--primary-color));
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 5px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .payment-services {
                flex-direction: column;
            }

            .checkout-form,
            .order-summary {
                padding: 20px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container checkout-container">
        <h1 class="page-title">Checkout</h1>

        <div class="row">
            <div class="col-lg-7">
                <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
                    @csrf
                    <div class="checkout-form">
                        <h3 class="section-title">Delivery Information</h3>

                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <input type="text" name="address" id="address" class="form-control"
                                    value="{{ old('address', $savedAddress ?? '') }}"
                                    placeholder="Enter your full address">
                                <div class="invalid-feedback" id="address-error">Address is required.</div>
                            </div>

                            <div class="col-md-12">
                                <label for="phone" class="form-label">Contact Number</label>
                                <input type="text" name="phone" id="phone" class="form-control"
                                    value="{{ old('phone', $savedPhone ?? '') }}"
                                    placeholder="Enter your phone number">
                                <div class="invalid-feedback" id="phone-error">Phone number is required.</div>
                            </div>
                        </div>

                        <h3 class="section-title">Payment Method</h3>

                        <h3 class="section-title">Promo Code</h3>
                        <div class="row mb-4">
                            <div class="col-md-8 mb-2 mb-md-0">
                                <input type="text" name="promo_code" class="form-control"
                                    value="{{ $promoCode?->code ?? session('checkout_promo_code') }}"
                                    placeholder="Enter promo code">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-outline-success w-100"
                                    form="checkout-form"
                                    formaction="{{ route('checkout.promo') }}"
                                    formnovalidate>
                                    Apply
                                </button>
                            </div>
                            @if($promoCode)
                                <div class="col-12 mt-2 text-success small">
                                    <i class="fas fa-tag me-1"></i> {{ $promoCode->code }} applied.
                                </div>
                            @endif
                        </div>

                        <div class="payment-methods">
                            <div class="payment-option">
                                <input type="radio" name="payment_method" value="cod" id="cod" checked>
                                <label for="cod">Cash on Delivery</label>
                            </div>

                            <div class="payment-option">
                                <input type="radio" name="payment_method" value="online" id="online" {{ empty($onlinePaymentsEnabled) ? 'disabled' : '' }}>
                                <label for="online">Online Payment</label>
                                @if(empty($onlinePaymentsEnabled))
                                    <span class="ms-2 text-muted small">Unavailable</span>
                                @endif
                            </div>
                        </div>

                        <!-- Online Payment Options (Hidden Initially) -->
                        <div id="online-payment-options" style="display: none;">
                            <h4 class="mt-4 mb-3">Select Payment Service</h4>

                            <div class="payment-services">
                                <div class="payment-card" id="easypaisa-card" onclick="selectPaymentService('easypaisa')">
                                    <div class="payment-card-title">Easypaisa</div>
                                    <div class="payment-card-account">Account: 0345-1234567</div>
                                </div>

                                <div class="payment-card" id="jazzcash-card" onclick="selectPaymentService('jazzcash')">
                                    <div class="payment-card-title">JazzCash</div>
                                    <div class="payment-card-account">Account: 0300-7654321</div>
                                </div>
                            </div>

                            <!-- Transaction ID Input (Initially Hidden) -->
                            <div class="mt-4" id="transaction-id-input" style="display: none;">
                                <label for="transaction_id" class="form-label">Transaction ID</label>
                                <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                                    placeholder="Enter your transaction ID">
                                <div class="invalid-feedback" id="transaction-error">Transaction ID is required.</div>
                            </div>
                        </div>
                    </div>

                    <!-- No need for service_id since we process all cart items -->
                </form>
            </div>

            <div class="col-lg-5">
                <div class="order-summary">
                    <h3 class="section-title">Order Summary</h3>
                    <div class="alert alert-success py-2 mb-3">
                        <i class="fas fa-clock me-1"></i> Estimated delivery: {{ $estimatedDeliveryWindow ?? '30-45 minutes' }}
                    </div>

                    <div class="order-item">
                        @foreach ($cart as $item)
                            <div class="order-row">
                                <span class="item-name">
                                    {{ $item['name'] }} ({{ $item['quantity'] }}x)
                                    @if(!empty($item['unit_type']))
                                        <small class="text-muted d-block">{{ $item['unit_type'] }}</small>
                                    @endif
                                    @if(!empty($item['seller_name']))
                                        <small class="text-muted d-block">From {{ $item['seller_name'] }}</small>
                                    @endif
                                    @if(!empty($item['stock_warning']))
                                        <small class="text-danger d-block">{{ $item['stock_warning'] }}</small>
                                    @endif
                                </span>
                                <span class="item-price">{{ $item['quantity'] * $item['price'] }} PKR</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="order-row">
                        <span>Subtotal</span>
                        <span>{{ number_format($subtotal ?? 0, 2) }} PKR</span>
                    </div>
                    <div class="order-row">
                        <span>Delivery Fee</span>
                        <span>{{ ($deliveryFee ?? 0) > 0 ? number_format($deliveryFee, 2) . ' PKR' : 'Free' }}</span>
                    </div>
                    <div class="order-row">
                        <span>Discount</span>
                        <span>{{ number_format($discount ?? 0, 2) }} PKR</span>
                    </div>

                    <div class="order-total">
                        <span class="total-label">Total</span>
                        <span class="total-value">{{ number_format($total ?? 0, 2) }} PKR</span>
                    </div>

                    <button type="submit" class="place-order-btn" form="checkout-form">
                        <i class="fas fa-check-circle me-2"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('checkout-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const address = document.getElementById('address');
            const phone = document.getElementById('phone');
            const transactionId = document.getElementById('transaction_id');
            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const paymentMethod = selectedPaymentMethod ? selectedPaymentMethod.value : 'cod';
            let isValid = true;

            // Reset validation messages
            document.getElementById('address-error').style.display = 'none';
            document.getElementById('phone-error').style.display = 'none';
            if (document.getElementById('transaction-error')) {
                document.getElementById('transaction-error').style.display = 'none';
            }

            // Validate address
            if (address.value.trim() === '') {
                document.getElementById('address-error').style.display = 'block';
                isValid = false;
            }

            // Validate phone
            if (phone.value.trim() === '') {
                document.getElementById('phone-error').style.display = 'block';
                isValid = false;
            }

            // Validate transaction ID if online payment selected
            if (paymentMethod === 'online' && transactionId.value.trim() === '') {
                document.getElementById('transaction-error').style.display = 'block';
                isValid = false;
            }

            if (isValid) {
                this.submit();
            }
        });

        // Toggle payment options visibility
        document.getElementById('online')?.addEventListener('change', function () {
            if (!this.disabled) {
                document.getElementById('online-payment-options').style.display = 'block';
            }
        });

        document.getElementById('cod').addEventListener('change', function () {
            document.getElementById('online-payment-options').style.display = 'none';
            document.getElementById('transaction-id-input').style.display = 'none';

            // Reset payment service selections
            const paymentCards = document.querySelectorAll('.payment-card');
            paymentCards.forEach(card => card.classList.remove('active'));
        });

        // Handle payment service selection
        function selectPaymentService(service) {
            const paymentCards = document.querySelectorAll('.payment-card');
            paymentCards.forEach(card => card.classList.remove('active'));

            document.getElementById(`${service}-card`).classList.add('active');
            document.getElementById('transaction-id-input').style.display = 'block';
        }
    </script>
@endsection
