<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakala Express OTP Email</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .email-header {
            background: linear-gradient(90deg, #007bff, #6c63ff);
            padding: 15px;
            color: #ffffff;
            border-radius: 8px 8px 0 0;
            font-size: 18px;
            font-weight: bold;
        }

        .email-content {
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
        }

        .email-content p {
            margin: 0 0 10px;
        }

        .otp-code {
            display: inline-block;
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            background: #f0f8ff;
            padding: 10px 20px;
            border-radius: 4px;
            margin: 15px 0;
        }

        .email-footer {
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }

        .email-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            Welcome to Bakala Express
        </div>
        <div class="email-content">
            <p>Thank you for registering with Bakala Express! Use the OTP below to complete your registration process:</p>
            <div class="otp-code">{{ $otp }}</div>
            <p>If you did not request this OTP, please ignore this email.</p>
        </div>
        <div class="email-footer">
            &copy; {{ date('Y') }} Bakala Express. All Rights Reserved. <br>
            Need help? <a href="mailto:support@bakalaexpress.com">Contact Support</a>
        </div>
    </div>
</body>
</html>
