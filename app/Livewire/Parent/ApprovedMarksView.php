<?php

namespace App\Livewire\Parent;

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Term;
use Livewire\Component;

/**
 * Parent view: approved CA results or approved exam results by term and school year.
 * Type is 'ca' or 'exam'. Parent selects school year and term, then sees a table of approved marks.
 */
class ApprovedMarksView extends Component
{
    /** 'ca' or 'exam' */
    public string $type = 'ca';

    public ?string $schoolYearId = null;

    public ?string $termId = null;

    public function mount(): void
    {
        if (! auth()->user()->isParent()) {
            abort(403, 'Only parents can view this page.');
        }
        $routeType = request()->route('type', 'ca');
        $this->type = $routeType === 'exam' ? 'exam' : 'ca';
    }

    public function getSchoolYearsProperty()
    {
        $user = auth()->user();
        if (! $user->parentProfile) {
            return collect();
        }

        $studentIds = $user->parentProfile->students()->pluck('students.id');
        $yearIds = Enrollment::where('is_active', true)
            ->whereIn('student_id', $studentIds)
            ->distinct()
            ->pluck('school_year_id')
            ->filter();

        return SchoolYear::whereIn('id', $yearIds)->orderByDesc('starts_at')->get();
    }

    public function getTermsProperty()
    {
        if (! $this->schoolYearId) {
            return collect();
        }

        return Term::where('school_year_id', $this->schoolYearId)
            ->orderBy('number')
            ->get();
    }

    /** Rows for the table: student_name, subject_name, mark (CA or exam). Only approved. */
    public function getRowsProperty()
    {
        $user = auth()->user();
        if (! $user->parentProfile || ! $this->schoolYearId || ! $this->termId) {
            return collect();
        }

        $studentIds = $user->parentProfile->students()->pluck('students.id');

        $enrollments = Enrollment::where('is_active', true)
            ->where('school_year_id', $this->schoolYearId)
            ->whereIn('student_id', $studentIds)
            ->with([
                'student',
                'termReports' => function ($q) {
                    $q->where('term_id', $this->termId)
                        ->with(['subjectReports' => function ($q) {
                            $q->with('subject');
                            if ($this->type === 'ca') {
                                $q->whereNotNull('ca_approved_at')->whereNotNull('ca_mark');
                            } else {
                                $q->whereNotNull('exam_approved_at')->whereNotNull('exam_mark');
                            }
                        }]);
                },
            ])
            ->get();

        $rows = collect();
        foreach ($enrollments as $enrollment) {
            $termReport = $enrollment->termReports->first();
            if (! $termReport) {
                continue;
            }
            $studentName = $enrollment->student->first_name . ' ' . $enrollment->student->last_name;
            if ($enrollment->student->other_names) {
                $studentName .= ' ' . $enrollment->student->other_names;
            }
            foreach ($termReport->subjectReports as $sr) {
                $mark = $this->type === 'ca' ? $sr->ca_mark : $sr->exam_mark;
                $rows->push([
                    'student_name' => $studentName,
                    'subject_name' => $sr->subject->name,
                    'mark' => $mark !== null ? (string) round((float) $mark, 2) : '–',
                ]);
            }
        }

        return $rows;
    }

    public function updatedSchoolYearId(): void
    {
        $this->termId = null;
    }

    public function render()
    {
        $title = $this->type === 'ca' ? 'View CA results' : 'View exam results';
        return view('livewire.parent.approved-marks-view', [
            'schoolYears' => $this->schoolYears,
            'terms' => $this->terms,
            'rows' => $this->rows,
        ])->layout('layouts.dashboard', [
            'headerTitle' => $title,
            'headerSubtitle' => 'Select school year and term to see approved marks.',
        ]);
    }
}
