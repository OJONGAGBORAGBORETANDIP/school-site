<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Portal - Report Card System</title>

    <!-- Tailwind via CDN for simplicity -->
    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-lg font-bold text-blue-700">Teacher Portal</h1>
                <p class="text-xs text-gray-500 mt-1">Report Card Management</p>
            </div>

            <nav class="mt-4 space-y-1">
                {{-- Dashboard - to be implemented --}}
                {{-- <a href="{{ route('teacher.dashboard') }}" class="block px-6 py-2 text-sm hover:bg-blue-50 {{ request()->routeIs('teacher.dashboard') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700' }}">
                    Dashboard
                </a> --}}
                {{-- My Classes - to be implemented --}}
                {{-- <a href="{{ route('teacher.classes') }}" class="block px-6 py-2 text-sm hover:bg-blue-50 {{ request()->routeIs('teacher.classes') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700' }}">
                    My Classes
                </a> --}}
                <a href="{{ route('teacher.marks-entry') }}" class="block px-6 py-2 text-sm hover:bg-blue-50 {{ request()->routeIs('teacher.marks-entry') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700' }}">
                    Marks Entry
                </a>
                {{-- Attendance - to be implemented --}}
                {{-- <a href="{{ route('teacher.attendance') }}" class="block px-6 py-2 text-sm hover:bg-blue-50 {{ request()->routeIs('teacher.attendance') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700' }}">
                    Attendance
                </a> --}}
            </nav>
        </aside>

        <!-- Main content -->
        <main class="flex-1">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            @yield('title', 'Teacher Area')
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            @yield('subtitle')
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">
                            {{ auth()->user()->name ?? 'Teacher' }}
                        </span>
                        <form method="POST" action="{{ route('teacher.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <section class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </section>
        </main>
    </div>

    @livewireScripts
</body>
</html>

