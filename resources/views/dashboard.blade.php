@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard')
@section('header-subtitle', 'Welcome back')

@section('content')
    <div class="space-y-10">
        @if(auth()->user()->isTeacher())
            <section class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Teacher</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your assigned classes, subjects, and students.</p>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned classes</h4>
                        @if($assignedSections->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No classes assigned yet.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($assignedSections as $section)
                                    <li class="flex items-center justify-between py-2 px-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <span class="text-gray-900 dark:text-gray-100">{{ $section->label }}@if($section->schoolClass) <span class="text-gray-500 dark:text-gray-400">({{ $section->schoolClass->name }})</span>@endif</span>
                                        <a href="{{ route('teacher.class-details', $section) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Class details & students</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned subjects</h4>
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
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Students in your classes</h4>
                        @if(empty($sectionsWithStudents))
                            <p class="text-sm text-gray-500 dark:text-gray-400">No students in assigned classes.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($assignedSections as $section)
                                    @php $students = $sectionsWithStudents[$section->id] ?? collect(); @endphp
                                    <li class="flex items-center justify-between py-2 px-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
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
            </section>
        @endif

        @if(auth()->user()->isParent())
            <section class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Parent</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your children and report cards.</p>
                </div>
                <div class="p-6">
                    @if($parentStudents->isEmpty())
                        <p class="text-sm text-amber-700 dark:text-amber-400">No children linked yet. Contact the school to link your account.</p>
                    @else
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($parentStudents as $student)
                                <li class="py-3 flex justify-between items-center flex-wrap gap-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $student->first_name }} {{ $student->last_name }}</span>
                                    <div class="flex gap-2">
                                        @php
                                            $publishedReportTerms = $student->enrollments()
                                                ->where('is_active', true)
                                                ->with(['termReports' => fn ($q) => $q->with('term.schoolYear')->whereHas('term', fn ($t) => $t->whereNotNull('results_published_at'))])
                                                ->get()
                                                ->flatMap->termReports
                                                ->unique('term_id')
                                                ->sortByDesc(fn ($tr) => $tr->term_id)
                                                ->take(5);
                                        @endphp
                                        @forelse($publishedReportTerms as $tr)
                                            <a href="{{ route('report-card.show', [$student, $tr->term]) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">{{ $tr->term->name }} {{ $tr->term->schoolYear->name ?? '' }}</a>
                                        @empty
                                            <span class="text-sm text-gray-500">No report cards published yet</span>
                                        @endforelse
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endif

        @if(!auth()->user()->isTeacher() && !auth()->user()->isParent())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700">
                {{ __("You're logged in.") }}
            </div>
        @endif
    </div>
@endsection
