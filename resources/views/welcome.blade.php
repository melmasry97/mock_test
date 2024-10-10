<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to REPT</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 400px;
            height: 300px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f5f5f5;
        }
        .header {
            width: 100%;
            height: 60px;
            background-color: #d3d3d3;
        }
        h1 {
            position: absolute;
            top: 100px;
            width: 100%;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #333333;
        }
        .btn {
            position: absolute;
            top: 160px;
            width: 90px;
            height: 40px;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            font-size: 16px;
        }
        .login-btn {
            left: 100px;
            background-color: #c07a3a;
            color: white;
        }
        .register-btn {
            left: 210px;
            background-color: white;
            color: #c07a3a;
            border: 2px solid #c07a3a;
        }
        .admin-login {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #888888;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"></div>
        <h1>REPT</h1>
        <a href="{{ route('filament.user.auth.login') }}" class="btn login-btn">Login</a>
        <a href="{{ route('filament.user.auth.register') }}" class="btn register-btn">Register</a>
        <a href="{{ route('filament.admin.auth.login') }}" class="admin-login">Login as admin</a>
    </div>
</body>
</html>
