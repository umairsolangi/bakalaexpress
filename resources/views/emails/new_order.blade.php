<!DOCTYPE html>
<html>
<head>
    <style>
        /* Add your email-specific CSS styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .email-container {
            margin: 0 auto;
            padding: 20px;
            max-width: 600px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 5px;
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px 5px 0 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #aaaaaa;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>New Order Received</h1>
        </div>
        <p>Hello {{ $sellerName }},</p>
        <p>You have received a new order:</p>
        <p><strong>Order ID:</strong> {{ $orderId }}</p>
        <p><strong>Order Details:</strong></p>
        <ul>
            @foreach($orderDetails as $detail)
                <li>
                    Service ID: {{ $detail['service_id'] }}, 
                    Quantity: {{ $detail['quantity'] }}, 
                    Price: {{ $detail['price'] }}
                </li>
            @endforeach
        </ul>
        <p>Click the button below to view the order:</p>
        <p>
        <a href="{{ url('/seller/order/' . $orderId . '/handle') }}" 
        style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                View Order
            </a>
        </p>
        <div class="footer">
            <p>Thank you for using Bakala Express!</p>
        </div>
    </div>
</body>
</html>
