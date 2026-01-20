@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="login-container">
    <div class="login-wrapper">
        <!-- Left Side - Login Form -->
        <div class="login-form-section">
            <div class="login-form-content">
                <!-- Logo and Title -->
                <div class="login-header">
                    <div class="logo-container">
                        <div class="logo-icon">E</div>
                        <div class="logo-text">
                            <h1 class="logo-title">FITNESS</h1>
                            <p class="logo-subtitle">Gym management</p>
                        </div>
                    </div>
                    <h2 class="login-title">Log in</h2>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf

                    <!-- Email Address -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            autocomplete="email"
                            placeholder="Enter your email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Enter your password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me and Forgot Password -->
                    <div class="form-options">
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="remember" 
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Keep me logged in
                            </label>
                        </div>
                        <a href="#" class="forgot-password-link">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-login">
                            Log in
                        </button>
                    </div>

                    <!-- Register Link - Removed as registration is handled by admins -->
                    {{-- <div class="register-link">
                        <p>Don't have an account? <a href="{{ route('register') }}">Register</a></p>
                    </div> --}}
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    <p>Terms of Use | Privacy Policy</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Background Image -->
        <div class="login-image-section">
            @php
                $loginImagePath = public_path('assets/images/login-background.jpg');
                $gymImagePath = public_path('assets/images/gym-background.jpg');
                $imagePath = null;
                $imageUrl = null;
                
                if (file_exists($loginImagePath)) {
                    $imageUrl = asset('assets/images/login-background.jpg');
                } elseif (file_exists($gymImagePath)) {
                    $imageUrl = asset('assets/images/gym-background.jpg');
                }
            @endphp
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="Fitness Background" class="login-background-image">
            @else
                <div class="login-background-gradient"></div>
            @endif
            <div class="login-image-overlay"></div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordEye = document.getElementById('password-eye');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordEye.classList.remove('fa-eye');
        passwordEye.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordEye.classList.remove('fa-eye-slash');
        passwordEye.classList.add('fa-eye');
    }
}
</script>
@endsection
