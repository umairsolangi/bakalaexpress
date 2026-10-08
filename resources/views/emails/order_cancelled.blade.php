<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7fa; color: #333; margin: 0; padding: 0; }
        .email-container { margin: 20px auto; padding: 0; max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; text-align: center; padding: 30px 20px; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 8px 0 0; font-size: 14px; opacity: 0.9; }
        .body { padding: 30px; }
        .body p { line-height: 1.6; margin-bottom: 16px; }
        .order-info { background-color: #fef2f2; border-radius: 8px; padding: 16px; margin-bottom: 20px; border: 1px solid #fecaca; }
        .order-info .label { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; }
        .order-info .value { font-size: 14px; font-weight: 600; color: #991b1b; margin-top: 4px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #9ca3af; border-top: 1px solid #f3f4f6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>❌ Order Cancelled</h1>
            <p>Your order has been cancelled.</p>
        </div>
        <div class="body">
            <p>Hi {{ $userName }},</p>
            <p>We're sorry to inform you that your order <strong>#{{ $order->id }}</strong> from <strong>{{ $order->seller?->name ?? 'the seller' }}</strong> has been {{ $order->status === 'rejected' ? 'rejected by the seller' : 'cancelled' }}.</p>

            @if($order->cancellation_reason)
                <div class="order-info">
                    <div class="label">Reason</div>
                    <div class="value">{{ $order->cancellation_reason }}</div>
                </div>
            @endif

            <div class="order-info">
                <div class="label">Order Total</div>
                <div class="value">{{ number_format($order->total_amount) }} PKR</div>
            </div>

            <p>If you paid online, your refund will be processed within 3-5 business days.</p>

            <p style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/') }}" class="btn">🛒 Continue Shopping</a>
            </p>
        </div>
        <div class="footer">
            <p>Need help? Contact our support team. — Bakala Express</p>
        </div>
    </div>
</body>
</html>
