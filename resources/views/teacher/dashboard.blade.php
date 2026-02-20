@extends('layouts.teacher')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard')
@section('header-subtitle', 'Your assigned classes, subjects, and students')

@section('content')
    <div class="space-y-8">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Assigned classes</h3>
            @if($assignedSections->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No classes assigned yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($assignedSections as $section)
                        <li class="flex items-center justify-between py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <span class="text-gray-900 dark:text-gray-100">{{ $section->label }}@if($section->schoolClass) <span class="text-gray-500 dark:text-gray-400">({{ $section->schoolClass->name }})</span>@endif</span>
                            <a href="{{ route('teacher.class-details', $section) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Class details & students</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Assigned subjects</h3>
            @if($assignedSubjects->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No subjects assigned yet.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($assignedSubjects as $subject)
                        <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1 text-sm text-gray-800 dark:text-gray-200">{{ $subject->name }} ({{ $subject->code }})</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Students in your classes</h3>
            @if(empty($sectionsWithStudents))
                <p class="text-sm text-gray-500 dark:text-gray-400">No students in assigned classes.</p>
            @else
                <ul class="space-y-3">
                    @foreach($assignedSections as $section)
                        @php $students = $sectionsWithStudents[$section->id] ?? collect(); @endphp
                        <li class="flex items-center justify-between py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $section->label }}</span>
                            @if($students->isEmpty())
                                <span class="text-sm text-gray-500">No students</span>
                            @else
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $students->count() }} student(s)</span>
                                <a href="{{ route('teacher.class-details', $section) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View list</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <a href="{{ route('teacher.marks-entry') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Marks entry</a>
        </div>
    </div>
@endsection
