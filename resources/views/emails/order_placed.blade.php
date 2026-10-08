<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7fa; color: #333; margin: 0; padding: 0; }
        .email-container { margin: 20px auto; padding: 0; max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; text-align: center; padding: 30px 20px; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 8px 0 0; font-size: 14px; opacity: 0.9; }
        .body { padding: 30px; }
        .body p { line-height: 1.6; margin-bottom: 16px; }
        .order-info { background-color: #f9fafb; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .order-info .label { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.5px; }
        .order-info .value { font-size: 16px; font-weight: 700; color: #111827; margin-top: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .items-table th { text-align: left; padding: 10px 12px; background-color: #f3f4f6; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; }
        .items-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .items-table .price { text-align: right; font-weight: 600; color: #10b981; }
        .total-row { background-color: #f0fdf4; }
        .total-row td { font-weight: 700; font-size: 16px; color: #059669; }
        .btn { display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #9ca3af; border-top: 1px solid #f3f4f6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>✅ Order Placed Successfully!</h1>
            <p>Thank you for your order, {{ $userName }}.</p>
        </div>
        <div class="body">
            <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                <div class="order-info" style="flex: 1;">
                    <div class="label">Order ID</div>
                    <div class="value">#{{ $order->id }}</div>
                </div>
                <div class="order-info" style="flex: 1;">
                    <div class="label">Seller</div>
                    <div class="value">{{ $order->seller?->name ?? 'N/A' }}</div>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->item_name ?? $item->product?->name ?? 'Product' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="price">{{ number_format($item->quantity * $item->price) }} PKR</td>
                        </tr>
                    @endforeach
                    @if($order->delivery_charges > 0)
                        <tr>
                            <td colspan="2">Delivery Charges</td>
                            <td class="price">{{ number_format($order->delivery_charges) }} PKR</td>
                        </tr>
                    @endif
                    @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="2">Discount</td>
                            <td class="price" style="color: #ef4444;">-{{ number_format($order->discount_amount) }} PKR</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="2"><strong>Total</strong></td>
                        <td class="price">{{ number_format($order->total_amount) }} PKR</td>
                    </tr>
                </tbody>
            </table>

            <div class="order-info">
                <div class="label">Delivery Address</div>
                <div class="value" style="font-size: 14px;">{{ $order->address }}</div>
            </div>

            @if($order->estimated_delivery_at)
                <div class="order-info">
                    <div class="label">Estimated Delivery</div>
                    <div class="value" style="font-size: 14px;">{{ $order->estimated_delivery_at->format('h:i A, M d, Y') }}</div>
                </div>
            @endif

            <p style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/order/' . $order->id) }}" class="btn">View Order Details</a>
            </p>
        </div>
        <div class="footer">
            <p>Thank you for shopping with Bakala Express! 🛒</p>
        </div>
    </div>
</body>
</html>
