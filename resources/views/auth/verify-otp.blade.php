<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #fff;
        }

        header {

            background-color: #007bff;
            padding: 15px 20px;
            color: #fff;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        footer {
            margin-top: auto;
            background-color: #007bff;
            padding: 10px 20px;
            color: #fff;
            text-align: center;
            font-size: 14px;
        }

        .verify-otp-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: #333;
            margin: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .verify-otp-container h2 {
            margin-bottom: 25px;
            font-size: 24px;
            color: #333;
        }

        .verify-otp-container label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            color: #555;
        }

        .verify-otp-container input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            margin-bottom: 20px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .verify-otp-container input[type="text"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        .verify-otp-container button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #007bff, #6c63ff);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .verify-otp-container button:hover {
            background: linear-gradient(90deg, #0056b3, #4b47e0);
        }

        .error {
            color: #ff4d4d;
            font-size: 13px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header>
        Welcome to Bakala Express
    </header>

    <div class="verify-otp-container">
        <h2>Verify OTP</h2>
        <form action="{{ route('verify.otp') }}" method="POST">
            @csrf
            <label for="otp">Enter OTP</label>
            <input type="text" id="otp" name="otp" placeholder="Enter your OTP" required>
            @if($errors->has('otp'))
                <div class="error">{{ $errors->first('otp') }}</div>
            @endif
            <button type="submit">Verify OTP</button>
        </form>
    </div>

    <footer>
        &copy; {{ date('Y') }} Bakala Express. All Rights Reserved.
    </footer>
</body>
</html>
