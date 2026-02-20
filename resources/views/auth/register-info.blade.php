@extends('layouts.guest')

@section('title', 'Register — ' . config('app.name'))

@section('content')
    <div class="max-w-md space-y-6">
        <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Register</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Account registration is managed by the school. To get an account (as a parent, teacher, or staff), please contact the school administrator.
        </p>
        <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">Go to Log in</a>
    </div>
@endsection
