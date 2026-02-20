<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            {{-- Teacher section: assigned classes, subjects, students, class details per term --}}
            @if(auth()->user()->isTeacher())
                <section class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Teacher</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your assigned classes, subjects, and students.</p>
                        </div>
                        <a href="{{ route('teacher.dashboard') }}" class="shrink-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Teacher dashboard (sidebar)</a>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assigned classes</h4>
                            @if($assignedSections->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400">No classes assigned yet.</p>
                            @else
                                <ul class="space-y-1">
                                    @foreach($assignedSections as $section)
                                        <li class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $section->label }}
                                            @if($section->schoolClass)
                                                <span class="text-gray-500">({{ $section->schoolClass->name ?? 'Class' }})</span>
                                            @endif
                                            — <a href="{{ route('teacher.class-details', $section) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Class details & students</a>
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
                                <ul class="flex flex-wrap gap-2">
                                    @foreach($assignedSubjects as $subject)
                                        <li class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-sm text-gray-800 dark:text-gray-200">
                                            {{ $subject->name }} ({{ $subject->code }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Students in your classes</h4>
                            @if(empty($sectionsWithStudents))
                                <p class="text-sm text-gray-500 dark:text-gray-400">No students in assigned classes.</p>
                            @else
                                <ul class="space-y-3">
                                    @foreach($assignedSections as $section)
                                        @php $students = $sectionsWithStudents[$section->id] ?? collect(); @endphp
                                        <li>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $section->label }}:</span>
                                            @if($students->isEmpty())
                                                <span class="text-sm text-gray-500">No students</span>
                                            @else
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $students->count() }} student(s) — </span>
                                                <a href="{{ route('teacher.class-details', $section) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View list</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('teacher.marks-entry') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                                Marks entry
                            </a>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Parent section: my children --}}
            @if(auth()->user()->isParent())
                <section class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                                    <li class="py-3 flex justify-between items-center">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $student->first_name }} {{ $student->last_name }}</span>
                                        <span class="text-sm text-gray-500">View report cards (coming soon)</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>
            @endif

            @if(!auth()->user()->isTeacher() && !auth()->user()->isParent())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in.") }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
