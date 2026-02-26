@extends('layouts.dashboard')

@section('title', 'Settings')
@section('header-title', 'Settings')
@section('header-subtitle', 'Settings')

@section('content')

<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        
    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" href="{{ route('appearance') }}" current>{{ __('Appearance') }}</flux:sidebar.item>
        <flux:sidebar.item icon="inbox" badge="12" href="{{ route('profile') }}">{{ __('Profile') }}</flux:sidebar.item>
        {{-- <flux:sidebar.item icon="document-text" href="#">Documents</flux:sidebar.item>
        <flux:sidebar.item icon="calendar" href="#">Calendar</flux:sidebar.item> --}}
    </flux:sidebar.nav>
</flux:sidebar>        
<flux:main>
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot ?? '' }}
    @endif
</flux:main>

@endsection