@extends('layouts.dashboard')

@section('title', 'Settings')
@section('header-title', 'Settings')
@section('header-subtitle', 'Manage your preferences')

@section('content')
@php
    $isAppearance = request()->routeIs('appearance');
    $isProfile = request()->routeIs('profile');
@endphp
<div class="flex gap-6 min-h-0">
    {{-- Settings sidebar: Appearance, Profile (no tabs - use nav links) --}}
    <flux:sidebar sticky collapsible="mobile" class="shrink-0 w-56 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:sidebar.nav>
            @if($isAppearance)
            <flux:sidebar.item icon="paint-brush" href="{{ route('appearance') }}" current>{{ __('Appearance') }}</flux:sidebar.item>
            @else
            <flux:sidebar.item icon="paint-brush" href="{{ route('appearance') }}">{{ __('Appearance') }}</flux:sidebar.item>
            @endif
            @if($isProfile)
            <flux:sidebar.item icon="user" href="{{ route('profile') }}" current>{{ __('Profile') }}</flux:sidebar.item>
            @else
            <flux:sidebar.item icon="user" href="{{ route('profile') }}">{{ __('Profile') }}</flux:sidebar.item>
            @endif
        </flux:sidebar.nav>
    </flux:sidebar>

    {{-- Main content: child view yields 'settingsContent' (e.g. appearance form) --}}
    <div class="flex-1 min-w-0">
        @yield('settingsContent')
    </div>
</div>
@endsection
