<x-landing-layout>
    <div class="container">
        <div class="logo"></div>
        <h1 class="main-title">Report Card System</h1>
        <p class="subtitle">Digital platform for seamless academic reporting, progress tracking and parent-teacher
            communication at El-Nissi Bilingual School</p>

        <div class="flex justify-center items-center h-full w-full">
            <div class="login-form-container">

                <h2 class="form-title">Welcome Back</h2>
                <p class="form-subtitle">Sign in to your account</p>

                <div class="flex flex-col gap-4">
                    @csrf

                    <a href="{{ route('teacher.login') }}" class="login-submit">
                        <i class="fas fa-arrow-right"></i>
                        Login as Teacher
                    </a>
                    <a href="{{ route('filament.admin.auth.login') }}" class="login-submit">
                        <i class="fas fa-arrow-right"></i>
                        Login as Admin
                    </a>
                    <a href="{{ route('teacher.login') }}" class="login-submit">
                        <i class="fas fa-arrow-right"></i>
                        Login as Parent
                    </a>
                    <a href="{{ route('filament.headteacher.auth.login') }}" class="login-submit">
                        <i class="fas fa-arrow-right"></i>
                        Login as Headteacher
                    </a>
                </div>

                <div class="switch-link">
                    <a href="#" onclick="openRegisterModal(); closeLoginModal();">Need an account? Register</a>
                </div>
            </div>
        </div>

    </div>

    <script src="{{asset('js/welcome-scripts.js')}}"></script>
    <script src="https://kit.fontawesome.com/2c8a7fee58.js" crossorigin="anonymous"></script>
</x-landing-layout>