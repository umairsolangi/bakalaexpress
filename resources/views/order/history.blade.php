@extends('layouts.app')

@section('title', 'Order History - Bakala Express')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/styleHome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/orderTrack.css') }}">
    <style>
        .orders-container {
            min-height: calc(100vh - 400px);
            margin-top: 50px;
            margin-bottom: 50px;
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

        .empty-history {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .empty-history i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 1.5rem;
        }

        .empty-history p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .order-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .order-id {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .order-date {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .order-body {
            padding: 1.5rem;
        }

        .order-status {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .order-status i {
            margin-right: 0.5rem;
        }

        .order-total {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .order-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            font-size: 0.9rem;
            margin-left: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-view:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            color: white;
        }

        .btn-feedback {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }

        .btn-feedback:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
            color: white;
        }

        .btn-action i {
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container orders-container">
        <h1 class="page-title">Order History</h1>

        @if($orders->isEmpty())
            <div class="empty-history">
                <i class="fas fa-history"></i>
                <p>You have no past orders.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-basket me-2"></i> Start Shopping
                </a>
            </div>
        @else
            <div class="row">
                @foreach($orders as $order)
                    <div class="col-md-6 mb-4">
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">Order #{{ $order->id }}</div>
                                <div class="order-date">{{ date('d M Y', strtotime($order->created_at)) }}</div>
                            </div>
                            <div class="order-body">
                                @php
                                    $statusIcons = [
                                        'pending' => '<i class="bi bi-hourglass-split"></i>',
                                        'accepted' => '<i class="bi bi-check-circle"></i>',
                                        'pickup_departed' => '<i class="bi bi-truck"></i>',
                                        'picked_up' => '<i class="bi bi-basket"></i>',
                                        'started_washing' => '<i class="bi bi-water"></i>',
                                        'ironing' => '<i class="bi bi-crop"></i>',
                                        'ready_for_delivery' => '<i class="bi bi-box-seam"></i>',
                                        'delivered' => '<i class="bi bi-truck"></i>',
                                        'completed' => '<i class="bi bi-check-circle-fill"></i>',
                                        'rejected' => '<i class="bi bi-x-circle"></i>',
                                        'cancelled' => '<i class="bi bi-slash-circle"></i>'
                                    ];

                                    $icon = isset($statusIcons[$order->status]) ? $statusIcons[$order->status] : '<i class="bi bi-question-circle"></i>';
                                    $statusText = ucfirst($order->status);
                                @endphp

                                <div class="order-status status-{{ $order->status }}">{!! $icon !!} {{ $statusText }}</div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <div class="text-muted">Total Price</div>
                                        <div class="order-total">${{ number_format($order->total_amount, 2) }}</div>
                                    </div>

                                    <div class="order-actions">
                                        <a href="{{ route('order.show', $order->id) }}" class="btn-action btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if(in_array($order->status, ['delivered', 'completed']))
                                            <a href="{{ route('order.feedback', $order->id) }}" class="btn-action btn-feedback">
                                                <i class="fas fa-comment"></i> Feedback
                                            </a>
                                        @endif
                                        @if(in_array($order->status, ['delivered', 'completed']))
                                            <form action="{{ route('order.buyAgain', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-action btn-view border-0">
                                                    <i class="fas fa-rotate-right"></i> Buy Again
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
