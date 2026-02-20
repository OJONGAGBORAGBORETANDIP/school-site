<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Portal - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-white shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-lg font-bold text-emerald-700">Parent Portal</h1>
                <p class="text-xs text-gray-500 mt-1">{{ config('app.name') }}</p>
            </div>
            <nav class="mt-4 space-y-1">
                <a href="{{ route('parent.dashboard') }}" class="block px-6 py-2 text-sm hover:bg-emerald-50 {{ request()->routeIs('parent.dashboard') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'text-gray-700' }}">
                    My Dashboard
                </a>
            </nav>
        </aside>

        <main class="flex-1">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">@yield('title', 'Dashboard')</h2>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('parent.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Log out</button>
                        </form>
                    </div>
                </div>
            </header>

            <section class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
                    @endif
                    @yield('content')
                </div>
            </section>
        </main>
    </div>
    @livewireScripts
</body>
</html>
