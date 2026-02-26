@extends('layouts.dashboard')

@section('title', 'Report Card')
@section('header-title', 'Report Card')
@section('header-subtitle', $student->first_name . ' ' . $student->last_name . ' – ' . $term->name . ' ' . $term->schoolYear->name)

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('report-card.download', [$student, $term]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
            Download Report Card (PDF)
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Student info --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Student Information</h3>
            <dl class="mt-2 grid grid-cols-2 gap-2 text-sm">
                <div><dt class="text-gray-500 dark:text-gray-400">Name</dt><dd class="font-medium">{{ $student->first_name }} {{ $student->last_name }} @if($student->other_names){{ $student->other_names }}@endif</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Admission No.</dt><dd class="font-medium">{{ $student->admission_number }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Class</dt><dd class="font-medium">{{ $enrollment->classSection->label ?? $enrollment->classSection->name }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Term / Year</dt><dd class="font-medium">{{ $term->name }} – {{ $term->schoolYear->name }}</dd></div>
            </dl>
        </div>

        {{-- Subjects --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Academic Performance</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subject</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">CA</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Grade</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Remark</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Comment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($subjectReports as $sr)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $sr->subject->name }}</td>
                            <td class="px-4 py-2 text-sm">{{ $sr->ca_mark !== null ? number_format($sr->ca_mark, 2) : '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $sr->exam_mark !== null ? number_format($sr->exam_mark, 2) : '-' }}</td>
                            <td class="px-4 py-2 text-sm font-medium">{{ $sr->total_mark !== null ? number_format($sr->total_mark, 2) : '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $sr->grade ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $sr->remark ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $sr->teacher_comment ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><span class="text-sm text-gray-500 dark:text-gray-400">Overall Average</span><p class="text-lg font-semibold">{{ $termReport->average !== null ? number_format($termReport->average, 2) : '-' }}</p></div>
            <div><span class="text-sm text-gray-500 dark:text-gray-400">Class Position</span><p class="text-lg font-semibold">{{ $termReport->position ?? '-' }}</p></div>
            <div><span class="text-sm text-gray-500 dark:text-gray-400">Class Size</span><p class="text-lg font-semibold">{{ $termReport->class_size ?? '-' }}</p></div>
            <div><span class="text-sm text-gray-500 dark:text-gray-400">Class Average</span><p class="text-lg font-semibold">{{ $termReport->class_average !== null ? number_format($termReport->class_average, 2) : '-' }}</p></div>
        </div>

        {{-- Remarks --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Class Teacher Remark</h4>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $termReport->class_teacher_remark ?? '–' }}</p>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-3">Headteacher Remark</h4>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $termReport->headteacher_remark ?? '–' }}</p>
        </div>

        {{-- Attendance --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Attendance Summary</h3>
            <dl class="grid grid-cols-3 gap-4 text-sm">
                <div><dt class="text-gray-500 dark:text-gray-400">Present</dt><dd class="font-medium">{{ $attendanceSummary['present'] }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Absent</dt><dd class="font-medium">{{ $attendanceSummary['absent'] }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Late</dt><dd class="font-medium">{{ $attendanceSummary['late'] }}</dd></div>
            </dl>
        </div>

        {{-- Behaviour ratings --}}
        @if($behaviourRatings->isNotEmpty())
        <div class="px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Behaviour / Skills</h3>
            <ul class="space-y-1 text-sm">
                @foreach($behaviourRatings as $br)
                <li class="flex justify-between"><span>{{ $br->aspect }}</span><span>Rating: {{ $br->rating }}/5 @if($br->comment) – {{ $br->comment }}@endif</span></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
