<?php

namespace App\Livewire\Parent;

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
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

    /** Selected child (student id) to show results for. */
    public ?string $studentId = null;

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

    /** Children (students) that have an enrollment in the selected school year. */
    public function getChildrenProperty()
    {
        $user = auth()->user();
        if (! $user->parentProfile || ! $this->schoolYearId) {
            return collect();
        }

        $studentIds = $user->parentProfile->students()->pluck('students.id');

        return Student::whereIn('id', $studentIds)
            ->whereHas('enrollments', function ($q) {
                $q->where('is_active', true)->where('school_year_id', $this->schoolYearId);
            })
            ->orderBy('first_name')
            ->get();
    }

    /** Rows for the table: subject_name, mark. Only approved; only for selected child. */
    public function getRowsProperty()
    {
        $user = auth()->user();
        if (! $user->parentProfile || ! $this->schoolYearId || ! $this->termId || ! $this->studentId) {
            return collect();
        }

        $allowedStudentIds = $user->parentProfile->students()->pluck('students.id')->all();
        if (! in_array($this->studentId, $allowedStudentIds)) {
            return collect();
        }

        $enrollment = Enrollment::where('is_active', true)
            ->where('school_year_id', $this->schoolYearId)
            ->where('student_id', $this->studentId)
            ->with([
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
            ->first();

        if (! $enrollment) {
            return collect();
        }

        $termReport = $enrollment->termReports->first();
        if (! $termReport) {
            return collect();
        }

        $rows = collect();
        foreach ($termReport->subjectReports as $sr) {
            $mark = $this->type === 'ca' ? $sr->ca_mark : $sr->exam_mark;
            $rows->push([
                'subject_name' => $sr->subject->name,
                'mark' => $mark !== null ? (string) round((float) $mark, 2) : '–',
            ]);
        }

        return $rows;
    }

    public function updatedSchoolYearId(): void
    {
        $this->termId = null;
        $this->studentId = null;
    }

    public function render()
    {
        $title = $this->type === 'ca' ? 'View CA results' : 'View exam results';
        return view('livewire.parent.approved-marks-view', [
            'schoolYears' => $this->schoolYears,
            'terms' => $this->terms,
            'children' => $this->children,
            'rows' => $this->rows,
        ])->layout('layouts.dashboard', [
            'headerTitle' => $title,
            'headerSubtitle' => 'Select academic year, term and child to see approved marks.',
        ]);
    }
}
