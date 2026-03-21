<?php

namespace App\Livewire\Teacher;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Services\HeadteacherApprovalService;
use App\Models\SubjectReport;
use App\Models\Term;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ResultReview extends Component
{
    public $classSections = [];

    public $terms = [];

    public $selectedClassSection = null;

    public $selectedTerm = null;

    public function mount(): void
    {
        $teacherId = auth()->id();

        $this->classSections = ClassSection::whereHas('teacherAssignments', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
            ->with('schoolClass')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'label' => $section->label . ' - ' . $section->schoolClass->name,
                ];
            })
            ->toArray();

        $this->terms = Term::whereHas('schoolYear', fn ($q) => $q->where('is_current', true))
            ->with('schoolYear')
            ->get()
            ->map(function ($term) {
                return [
                    'id' => $term->id,
                    'label' => $term->name . ' - ' . $term->schoolYear->name,
                ];
            })
            ->toArray();
    }

    /**
     * Review summary for selected class + term: all subjects with entered/total counts and status (per subject).
     */
    public function getReviewSummary(): array
    {
        if (!$this->selectedClassSection || !$this->selectedTerm) {
            return [];
        }

        $teacherId = auth()->id();
        $enrollments = Enrollment::where('class_section_id', $this->selectedClassSection)
            ->where('is_active', true)
            ->get();
        $totalStudents = $enrollments->count();
        $enrollmentIds = $enrollments->pluck('id')->toArray();

        $assignments = DB::table('teacher_assignments')
            ->where('teacher_id', $teacherId)
            ->where('class_section_id', $this->selectedClassSection)
            ->join('subjects', 'teacher_assignments.subject_id', '=', 'subjects.id')
            ->select('subjects.id', 'subjects.name', 'subjects.code')
            ->get();

        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $this->selectedTerm)
            ->pluck('id');

        $summary = [];
        foreach ($assignments as $subject) {
            $entered = 0;
            if ($termReportIds->isNotEmpty()) {
                $entered = SubjectReport::whereIn('term_report_id', $termReportIds)
                    ->where('subject_id', $subject->id)
                    ->where(function ($q) {
                        $q->whereNotNull('ca_mark')->orWhereNotNull('exam_mark');
                    })
                    ->count();
            }
            $subjectSubmitted = $termReportIds->isNotEmpty() && SubjectReport::whereIn('term_report_id', $termReportIds)
                ->where('subject_id', $subject->id)
                ->whereNotNull('submitted_at')
                ->exists();
            $summary[] = [
                'subject_id' => $subject->id,
                'label' => "{$subject->name} ({$subject->code})",
                'entered' => $entered,
                'total' => $totalStudents,
                'status' => $subjectSubmitted ? 'submitted' : 'draft',
            ];
        }
        return $summary;
    }

    /**
     * Submit this subject's results for head teacher approval (from Result Review row).
     */
    public function submitSubjectForApproval(int $subjectId): void
    {
        if (!$this->selectedClassSection || !$this->selectedTerm) {
            session()->flash('error', 'Please select class and term.');
            return;
        }

        $enrollmentIds = Enrollment::where('class_section_id', $this->selectedClassSection)
            ->where('is_active', true)
            ->pluck('id');
        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $this->selectedTerm)
            ->pluck('id');

        if ($termReportIds->isEmpty()) {
            session()->flash('error', 'No term reports found for this class and term.');
            return;
        }

        $reports = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->get();

        foreach ($reports as $report) {
            $ca = $report->ca_mark !== null ? (float) $report->ca_mark : null;
            $exam = $report->exam_mark !== null ? (float) $report->exam_mark : null;
            if (($ca !== null && ($ca < 0 || $ca > 20)) || ($exam !== null && ($exam < 0 || $exam > 20))) {
                session()->flash('error', 'Some marks for this subject are out of range (Sequence 0–20, Exam 0–20). Fix them in Marks entry first.');
                return;
            }
        }

        SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->update(['submitted_at' => now()]);

        session()->flash('success', 'Results for this subject have been submitted for head teacher approval.');
    }

    /**
     * Class-level status for selected class+term: 'pending' when all subjects submitted but not yet approved by headmaster.
     */
    public function getClassApprovalStatus(): ?string
    {
        if (!$this->selectedClassSection || !$this->selectedTerm) {
            return null;
        }
        return app(HeadteacherApprovalService::class)->getClassStatus(
            (int) $this->selectedClassSection,
            (int) $this->selectedTerm
        );
    }

    public function render()
    {
        $reviewSummary = $this->getReviewSummary();
        $classApprovalStatus = $this->getClassApprovalStatus();

        return view('livewire.teacher.result-review', [
            'reviewSummary' => $reviewSummary,
            'classApprovalStatus' => $classApprovalStatus,
        ])->layout('layouts.dashboard', [
            'headerTitle' => 'Result review',
            'headerSubtitle' => 'Review all entered results by class and term before submission',
        ]);
    }
}
