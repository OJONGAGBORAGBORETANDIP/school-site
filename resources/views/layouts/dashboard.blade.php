{{-- Unified layout for parent and teacher: sidebar (nav by role) + header + main content. --}}
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
    <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo.png" name="Acme Inc." class="max-lg:hidden dark:hidden" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/dark-mode-logo.png" name="Acme Inc." class="max-lg:hidden! hidden dark:flex" />
        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="home" href="{{ route('dashboard') }}" current>{{ __('Dashboard') }}</flux:navbar.item>
            @if(auth()->user()->isTeacher())
            <flux:navbar.item icon="inbox" href="{{ route('teacher.marks-entry') }}" badge="12">Inbox</flux:navbar.item>
            <flux:navbar.item icon="document-text" href="{{ route('teacher.result-review') }}">Documents</flux:navbar.item>
            @endif
            <flux:navbar.item icon="calendar" href="#">Calendar</flux:navbar.item>
            <flux:separator vertical variant="subtle" class="my-2"/>
            <flux:dropdown class="max-lg:hidden">
                <flux:navbar.item icon:trailing="chevron-down">Favorites</flux:navbar.item>
                <flux:navmenu>
                    <flux:navmenu.item href="#">Marketing site</flux:navmenu.item>
                    <flux:navmenu.item href="#">Android app</flux:navmenu.item>
                    <flux:navmenu.item href="#">Brand guidelines</flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>
        </flux:navbar>
        <flux:spacer />
        <flux:navbar class="me-4">
            <flux:navbar.item icon="magnifying-glass" href="#" label="Search" />
            <flux:navbar.item class="max-lg:hidden" icon="cog-6-tooth" href="#" label="Settings" />
            <flux:navbar.item class="max-lg:hidden" icon="information-circle" href="#" label="Help" />
        </flux:navbar>
        <flux:dropdown position="top" align="start">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />
            <flux:menu>
                <flux:navbar.item class="max-lg:hidden" icon="cog-6-tooth" href="{{ route('settings') }}" label="Settings">Settings</flux:navbar.item>
                <flux:menu.radio.group>
                    <flux:menu.radio href="{{ route('profile') }}" checked>{{ auth()->user()->name }}</flux:menu.radio>
                    <flux:menu.radio href="{{ route('logout') }}" icon="arrow-right-start-on-rectangle">Logout</flux:menu.radio>
                </flux:menu.radio.group>
                <flux:menu.separator />
            </flux:menu>
        </flux:dropdown>
    </flux:header>
    <div class="min-h-screen flex">
        {{-- Sidebar: same for all; items vary by role --}}
        <aside class="w-64 shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ config('app.name') }}</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dashboard</p>
            </div>
        </aside>

        {{-- Main: top nav + content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@yield('header-title', $headerTitle ?? 'Dashboard')</h2>
                        @php $subtitle = $headerSubtitle ?? trim((string) $__env->yieldContent('header-subtitle')); @endphp
                        @if($subtitle !== '')
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ auth()->user()->name }}</span>
                        <a href="{{ route('profile') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Log out</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
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
            </main>
        </div>
    </div>
    @livewireScripts
    @fluxScripts
</body>
</html>
