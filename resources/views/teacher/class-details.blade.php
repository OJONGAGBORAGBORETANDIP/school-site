@extends('layouts.dashboard')

@section('title', $section->label . ' – Class details')
@section('header-title', $section->label . ($section->schoolClass ? ' — ' . $section->schoolClass->name : ''))
@section('header-subtitle', 'Class details and students by term')

@section('content')
    <div class="space-y-6">
        <p>
            <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">← Back to dashboard</a>
        </p>

        @if($currentYear)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Academic year: {{ $currentYear->name }}</h3>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Terms</h4>
                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    @foreach($terms as $term)
                        <li>Term {{ $term->number }}: {{ $term->name }} ({{ $term->starts_at?->format('M j') }} – {{ $term->ends_at?->format('M j, Y') }})</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Students in this class</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active enrollments by school year.</p>
            </div>
            <div class="p-6">
                @forelse($enrollmentsByYear as $yearId => $enrollments)
                    @php
                        $year = $enrollments->first()->schoolYear ?? null;
                    @endphp
                    <div class="mb-6 last:mb-0">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $year ? $year->name : 'School year #'.$yearId }}</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            @foreach($enrollments->sortBy(fn ($e) => $e->student->last_name ?? '') as $enrollment)
                                <li class="px-4 py-2 flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</span>
                                    @if($enrollment->student->admission_number)
                                        <span class="text-gray-500">#{{ $enrollment->student->admission_number }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No students enrolled in this class.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
