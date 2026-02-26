{{-- Flux sidebar layout: dashboard, marks-entry/result-review (teacher), settings. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') – {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <flux:sidebar.brand
                href="{{ route('dashboard') }}"
                logo="https://fluxui.dev/img/demo/logo.png"
                logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
                name="{{ config('app.name') }}"
            />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.search placeholder="Search..." />

        <flux:sidebar.nav>
            @php
                $isDashboard = request()->routeIs('dashboard');
                $isMarksEntry = request()->routeIs('teacher.marks-entry');
                $isResultReview = request()->routeIs('teacher.result-review');
                $isSettings = request()->routeIs('settings');
            @endphp
            @if($isDashboard)
            <flux:sidebar.item icon="home" href="{{ route('dashboard') }}" current>{{ __('Dashboard') }}</flux:sidebar.item>
            @else
            <flux:sidebar.item icon="home" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</flux:sidebar.item>
            @endif
            @if(auth()->user()->isTeacher())
                @if($isMarksEntry)
                <flux:sidebar.item icon="inbox" href="{{ route('teacher.marks-entry') }}" current>{{ __('Marks entry') }}</flux:sidebar.item>
                @else
                <flux:sidebar.item icon="inbox" href="{{ route('teacher.marks-entry') }}">{{ __('Marks entry') }}</flux:sidebar.item>
                @endif
                @if($isResultReview)
                <flux:sidebar.item icon="document-text" href="{{ route('teacher.result-review') }}" current>{{ __('Result review') }}</flux:sidebar.item>
                @else
                <flux:sidebar.item icon="document-text" href="{{ route('teacher.result-review') }}">{{ __('Result review') }}</flux:sidebar.item>
                @endif
            @endif
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            @if($isSettings)
            <flux:sidebar.item icon="cog-6-tooth" href="{{ route('settings') }}" current>{{ __('Settings') }}</flux:sidebar.item>
            @else
            <flux:sidebar.item icon="cog-6-tooth" href="{{ route('settings') }}">{{ __('Settings') }}</flux:sidebar.item>
            @endif
        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile avatar="https://fluxui.dev/img/demo/user.png" name="{{ auth()->user()->name }}" />
            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('profile') }}">{{ __('Profile') }}</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item icon="arrow-right-start-on-rectangle">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full text-left">{{ __('Log out') }}</button>
                    </form>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:dropdown position="top" align="start">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />
            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('profile') }}">{{ __('Profile') }}</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item icon="arrow-right-start-on-rectangle">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full text-left">{{ __('Log out') }}</button>
                    </form>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main>
        <header class="mb-6">
            <flux:heading size="xl" level="1">@yield('header-title', $headerTitle ?? __('Dashboard'))</flux:heading>
            @php $subtitle = $headerSubtitle ?? trim((string) $__env->yieldContent('header-subtitle')); @endphp
            @if($subtitle !== '')
                <flux:text class="mt-2 text-base text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</flux:text>
            @endif
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
        @endif

        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </flux:main>

    @livewireScripts
    @fluxScripts
</body>
</html>
