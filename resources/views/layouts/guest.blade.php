<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - @yield('title', 'Welcome')</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    
    <!-- Dynamic Theme Colors from Settings -->
    @php
        try {
            $settings = App\Models\Setting::current();
        } catch (\Exception $e) {
            $settings = null;
        }
    @endphp
    
    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#007bff' }};
            --secondary-color: {{ $settings->secondary_color ?? '#6c757d' }};
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .login-page {
            min-height: 100vh;
            background: #f5f5f5;
            padding: 0;
            overflow: hidden;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            position: relative;
        }
        
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .login-form-section {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            z-index: 10;
        }
        
        .login-form-content {
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: calc(100vh - 120px);
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }
        
        .login-header {
            margin-bottom: 40px;
            flex-shrink: 0;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
            font-family: 'Arial', sans-serif;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        
        .logo-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            line-height: 1;
        }
        
        .logo-subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
            text-transform: lowercase;
        }
        
        .login-title {
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .password-input-wrapper {
            position: relative;
        }
        
        .password-input-wrapper .form-control {
            padding-right: 50px;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }
        
        .form-check-label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            margin: 0;
        }
        
        .forgot-password-link {
            font-size: 14px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password-link:hover {
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
        }
        
        .register-link p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .login-footer {
            margin-top: auto;
            margin-bottom: 0;
            padding-top: 20px;
            padding-bottom: 0;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            flex-shrink: 0;
        }
        
        .login-footer p {
            font-size: 12px;
            color: #999;
            margin: 0;
        }
        
        .login-form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .login-image-section {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            z-index: 1;
        }
        
        .login-background-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            min-height: 100%;
            min-width: 100%;
            filter: blur(8px);
            transform: scale(1.1);
        }
        
        .login-background-gradient {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }
        
        .login-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 50%, rgba(240, 147, 251, 0.2) 100%);
            z-index: 2;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        
        @media (max-width: 992px) {
            .login-form-section {
                padding: 40px 30px;
            }
            
            .login-form-content {
                padding: 30px;
            }
        }
        
        @media (max-width: 576px) {
            .login-form-section {
                padding: 30px 20px;
            }
            
            .logo-title {
                font-size: 24px;
            }
            
            .login-title {
                font-size: 28px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="login-page">
    <!-- Flash Messages -->
    @if(session('status'))
        <div class="alert alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            {{ session('status') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Content -->
    @yield('content')
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8w3eMEfa0f3Dv0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    
    @stack('scripts')
</body>
</html> 