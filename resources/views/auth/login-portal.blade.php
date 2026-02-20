@extends('layouts.guest')

@section('title', 'Sign in — ' . config('app.name'))

@section('content')
    <div class="max-w-md space-y-6">
        <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Sign in</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Choose your portal to sign in:</p>
        <ul class="space-y-3">
            <li>
                <a href="{{ url('/admin/login') }}" class="block px-5 py-2.5 border border-[#19140035] dark:border-[#3E3E3A] rounded-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#1914000a] dark:hover:bg-[#3E3E3A] transition-colors">
                    Admin
                </a>
            </li>
            <li>
                <a href="{{ url('/headteacher/login') }}" class="block px-5 py-2.5 border border-[#19140035] dark:border-[#3E3E3A] rounded-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#1914000a] dark:hover:bg-[#3E3E3A] transition-colors">
                    Headteacher
                </a>
            </li>
            <li>
                <a href="{{ url('/teacher/marks-entry') }}" class="block px-5 py-2.5 border border-[#19140035] dark:border-[#3E3E3A] rounded-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#1914000a] dark:hover:bg-[#3E3E3A] transition-colors">
                    Teacher
                </a>
            </li>
        </ul>
        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Teachers: use the Teacher link; you will be asked to sign in if needed.</p>
    </div>
@endsection
