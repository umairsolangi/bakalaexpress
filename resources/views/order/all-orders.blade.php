@extends('layouts.app')

@section('title', 'Your Orders - Bakala Express')

@section('styles')
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

        .empty-orders {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .empty-orders i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 1.5rem;
        }

        .empty-orders p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .empty-orders-btn {
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition-normal);
        }

        .empty-orders-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .orders-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .orders-table {
            width: 100%;
            margin-bottom: 0;
        }

        .orders-table th {
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 15px;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .orders-table td {
            vertical-align: middle;
            padding: 15px;
            font-size: 0.95rem;
            color: var(--text-color);
            border-bottom: 1px solid var(--light-gray);
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .order-id {
            font-weight: 600;
            color: var(--primary-color);
        }

        .order-status {
            font-weight: 600;
            text-transform: capitalize;
            padding: 5px 10px;
            border-radius: var(--border-radius-md);
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            min-width: 120px;
            text-align: center;
        }

        .order-status i {
            margin-right: 6px;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.15);
            color: #f39c12;
        }

        .status-accepted {
            background-color: rgba(52, 152, 219, 0.15);
            color: var(--secondary);
        }

        .status-pickup_departed {
            background-color: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }

        .status-picked_up {
            background-color: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }

        .status-started_washing {
            background-color: rgba(52, 152, 219, 0.15);
            color: #2980b9;
        }

        .status-ironing {
            background-color: rgba(142, 68, 173, 0.15);
            color: #8e44ad;
        }

        .status-ready_for_delivery {
            background-color: rgba(142, 68, 173, 0.15);
            color: #9b59b6;
        }

        .status-delivered {
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--success);
        }

        .status-completed {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .status-rejected {
            background-color: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }

        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }

        .order-date {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .track-button {
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius-md);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition-normal);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .track-button:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
            color: white;
        }

        .track-button i {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .orders-table {
                display: block;
                overflow-x: auto;
            }

            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container orders-container">
        <h1 class="page-title">Your Orders</h1>

        @if($orders->isEmpty())
            <div class="empty-orders">
                <i class="fas fa-box-open"></i>
                <p>You have no orders yet.</p>
                <a href="{{ route('home') }}" class="empty-orders-btn">
                    <i class="fas fa-shopping-basket me-2"></i> Start Shopping
                </a>
            </div>
        @else
            <div class="orders-card">
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td class="order-id">#{{ $order->id }}</td>
                                    <td>
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
                                            $statusText = ucwords(str_replace('_', ' ', $order->status));
                                        @endphp
                                        <span class="order-status status-{{ $order->status }}">{!! $icon !!}
                                            {{ $statusText }}</span>
                                    </td>
                                    <td>{{ $order->orderItems ? $order->orderItems->count() : 0 }}</td>
                                    <td>{{ number_format($order->total_amount, 2) }} PKR</td>
                                    <td>
                                        <div class="order-date">{{ $order->created_at->format('d M Y') }}</div>
                                        <div class="order-date">{{ $order->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('order.track', $order) }}" class="track-button">
                                            <i class="fas fa-truck"></i> Track
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection