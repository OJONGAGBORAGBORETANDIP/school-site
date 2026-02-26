@extends('layouts.dashboard')

@section('title', 'Profile')
@section('header-title', 'Profile')
@section('header-subtitle', 'Change your password')

@section('content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Change password') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ensure your account uses a strong, unique password.') }}</p>

                @if (session('status') === 'password-updated')
                    <p class="mt-4 text-sm text-green-600 dark:text-green-400">{{ __('Password updated.') }}</p>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="current_password" :value="__('Current password')" />
                        <x-text-input id="current_password" name="current_password" type="password" class="block mt-1 w-full" autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('New password')" />
                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('Update password') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
@endsection
