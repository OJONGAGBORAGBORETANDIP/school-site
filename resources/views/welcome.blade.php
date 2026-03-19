<x-landing-layout>
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
            <a class="btn-secondary" href="{{ route('landing') }}">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="login-form-container">
            <span class="close-modal" onclick="closeLoginModal()">&times;</span>

            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Sign in to your account</p>

            @if ($errors->any() && !old('name'))
                <div style="margin-bottom: 16px; padding: 10px 12px; border-radius: 10px; background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fecaca; font-size: 14px;">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login-user') }}">
                @csrf
                <div class="form-group full-width">
                    <label>Email</label>
                    <input type="email" class="form-input" name="email" placeholder="Enter email" value="{{ old('email') }}" required>
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
                @if ($errors->any() && old('name'))
                    <div style="margin-bottom: 16px; padding: 10px 12px; border-radius: 10px; background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fecaca; font-size: 14px; text-align: left;">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form id="registerForm" method="POST" action="{{ route('register-user') }}">
                    @csrf
                    <div class="form-group full-width">
                        <label>Name</label>
                        <input type="text" class="form-input" name="name" placeholder="Enter name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group full-width">
                        <label>Email</label>
                        <input type="email" class="form-input" name="email" placeholder="Enter email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label>Password</label>
                        <input type="password" class="form-input" name="password" placeholder="Enter password" required>
                        @error('password')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label>Confirm Password</label>
                        <input type="password" class="form-input" name="password_confirmation"
                            placeholder="Confirm password" required>
                        @error('password_confirmation')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <button type="submit" class="login-submit">
                            <i class="fas fa-arrow-right"></i>
                            Register
                        </button>
                        <button type="button" class="login-submit" style="width: 100%; margin-top: 24px;"
                            onclick="closeRegisterModal()">
                            Back to Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{asset('js/welcome-scripts.js')}}"></script>
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if (old('name'))
                    openRegisterModal();
                @else
                    openLoginModal();
                @endif
            });
        </script>
    @endif
    <script src="https://kit.fontawesome.com/2c8a7fee58.js" crossorigin="anonymous"></script>
</x-landing-layout>