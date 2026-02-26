<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report Card – {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 20px; color: #111; }
        h1 { font-size: 18px; text-align: center; margin-bottom: 4px; }
        h2 { font-size: 14px; text-align: center; color: #333; margin-bottom: 20px; }
        .info { margin-bottom: 16px; }
        .info table { width: 100%; }
        .info td { padding: 2px 8px 2px 0; }
        .info .label { color: #555; width: 120px; }
        table.data { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.data th, table.data td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        table.data th { background: #e5e7eb; font-size: 10px; }
        .summary { margin: 16px 0; }
        .summary table { width: 100%; }
        .summary td { padding: 4px 12px 4px 0; }
        .section { margin-top: 16px; }
        .section h3 { font-size: 12px; margin-bottom: 6px; border-bottom: 1px solid #ccc; }
        .footer { margin-top: 24px; font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }}</h1>
    <h2>Term Report Card</h2>

    <div class="info">
        <table>
            <tr><td class="label">Student name</td><td><strong>{{ $student->first_name }} {{ $student->last_name }} @if($student->other_names){{ $student->other_names }}@endif</strong></td></tr>
            <tr><td class="label">Admission number</td><td>{{ $student->admission_number }}</td></tr>
            <tr><td class="label">Class</td><td>{{ $enrollment->classSection->label ?? $enrollment->classSection->name }}</td></tr>
            <tr><td class="label">Term / Year</td><td>{{ $term->name }} – {{ $term->schoolYear->name }}</td></tr>
        </table>
    </div>

    <h3 class="section">Academic Performance</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Subject</th>
                <th>CA</th>
                <th>Exam</th>
                <th>Total</th>
                <th>Grade</th>
                <th>Remark</th>
                <th>Comment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjectReports as $sr)
            <tr>
                <td>{{ $sr->subject->name }}</td>
                <td>{{ $sr->ca_mark !== null ? number_format($sr->ca_mark, 2) : '-' }}</td>
                <td>{{ $sr->exam_mark !== null ? number_format($sr->exam_mark, 2) : '-' }}</td>
                <td>{{ $sr->total_mark !== null ? number_format($sr->total_mark, 2) : '-' }}</td>
                <td>{{ $sr->grade ?? '-' }}</td>
                <td>{{ $sr->remark ?? '-' }}</td>
                <td>{{ $sr->teacher_comment ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Overall Average:</strong> {{ $termReport->average !== null ? number_format($termReport->average, 2) : '-' }}</td>
                <td><strong>Class Position:</strong> {{ $termReport->position ?? '-' }}</td>
                <td><strong>Class Size:</strong> {{ $termReport->class_size ?? '-' }}</td>
                <td><strong>Class Average:</strong> {{ $termReport->class_average !== null ? number_format($termReport->class_average, 2) : '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Remarks</h3>
        <p><strong>Class Teacher:</strong> {{ $termReport->class_teacher_remark ?? '–' }}</p>
        <p><strong>Headteacher:</strong> {{ $termReport->headteacher_remark ?? '–' }}</p>
    </div>

    <div class="section">
        <h3>Attendance Summary</h3>
        <p>Present: {{ $attendanceSummary['present'] }} &nbsp; Absent: {{ $attendanceSummary['absent'] }} &nbsp; Late: {{ $attendanceSummary['late'] }}</p>
    </div>

    @if($behaviourRatings->isNotEmpty())
    <div class="section">
        <h3>Behaviour / Skills</h3>
        <table class="data">
            <thead><tr><th>Aspect</th><th>Rating</th><th>Comment</th></tr></thead>
            <tbody>
                @foreach($behaviourRatings as $br)
                <tr><td>{{ $br->aspect }}</td><td>{{ $br->rating }}/5</td><td>{{ $br->comment ?? '–' }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('d M Y') }} – {{ config('app.name') }}
    </div>
</body>
</html>
