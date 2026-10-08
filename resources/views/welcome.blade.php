<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-image: url('../images/home-background.jpg') !important;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
            background-blend-mode: overlay;
        }

        .container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('/placeholder.svg?height=1080&width=1920');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .button-container {
            display: flex;
            gap: 20px;
        }

        .button {
            padding: 15px 30px;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #45a049;
        }

        .button.seller {
            background-color: #008CBA;
        }

        .button.seller:hover {
            background-color: #007B9E;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="button-container">
            <a href="{{ route('home') }}" class="button">Buyer</a>
            <a href="{{ Auth::guard('seller')->check() ? route('seller.panel') : route('login.seller') }}"
                class="button seller">Seller</a>
            <a href="{{ Auth::guard('rider')->check() ? route('rider.dashboard') : route('rider.login') }}"
                class="button" style="background-color: #f59e0b;">Rider</a>
        </div>
    </div>
</body>

</html>