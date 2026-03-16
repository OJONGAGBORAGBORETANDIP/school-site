<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Parent Portal – {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('css/styles.css')}}" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{asset('css/welcome-styles.css')}}">
    @endif
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

    <div class="container">
        <div class="logo"></div>
        <h1 class="main-title">Report Card System</h1>
        <p class="subtitle">Digital platform for seamless academic reporting, progress tracking and parent-teacher
            communication at El-Nissi Bilingual School</p>

        <div class="action-buttons">
            <button class="btn-primary" onclick="openLoginModal()">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
            <button class="btn-secondary" onclick="openRegisterModal()">
                <i class="fas fa-user-plus"></i>
                Register
            </button>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="login-form-container">
            <span class="close-modal" onclick="closeLoginModal()">&times;</span>

            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Sign in to your account</p>

            <form id="loginForm" method="POST" action="{{ route('login-user') }}">
                @csrf
                <div class="form-group full-width">
                    <label>Email</label>
                    <input type="email" class="form-input" name="email" placeholder="Enter email" required>
                </div>

                <div class="form-group full-width">
                    <label>Password</label>
                    <input type="password" class="form-input" name="password" placeholder="Enter password" required>
                </div>


                <button type="submit" class="login-submit">
                    <i class="fas fa-arrow-right"></i>
                    Sign In
                </button>
            </form>

            <div class="switch-link">
                <a href="#" onclick="openRegisterModal(); closeLoginModal();">Need an account? Register</a>
            </div>
        </div>
    </div>

    <!-- Register Modal (Placeholder) -->
    <div class="login-modal" id="registerModal">
        <div class="login-form-container" style="max-width:600px;">
            <span class="close-modal" onclick="closeRegisterModal()">&times;</span>

            <h2 class="form-title">Create Account</h2>
            <p class="form-subtitle">Join our school portal</p>

            <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.8);">
                <i class="fas fa-rocket" style="font-size: 64px; color: #4CAF50; margin-bottom: 24px;"></i>
                <form id="loginForm">
                    <form id="loginForm" method="POST" action="{{ route('register-user') }}">
                        @csrf
                        <div class="form-group full-width">
                            <label>Name</label>
                            <input type="text" class="form-input" name="name" placeholder="Enter name" required>
                            @error('name')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group full-width">
                            <label>Email</label>
                            <input type="email" class="form-input" name="email" placeholder="Enter email" required>
                            @error('email')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label>Password</label>
                            <input type="password" class="form-input" name="password" placeholder="Enter password"
                                required>
                            @error('password')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label>Confirm Password</label>
                            <input type="password" class="form-input" name="confirm_password"
                                placeholder="Confirm password" required>
                            @error('confirm_password')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <button type="submit" class="login-submit">
                                <i class="fas fa-arrow-right"></i>
                                Register
                            </button>
                            <button class="login-submit" style="width: 100%; margin-top: 24px;"
                                onclick="closeRegisterModal()">
                                Back to Login
                            </button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
    <script src="{{asset('js/welcome-scripts.js')}}"></script>
</body>

</html>