<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\SubjectReport;
use App\Models\Term;
use App\Models\TermReport;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Notifications\ReportApprovedNotification;
use App\Notifications\ReportRejectedNotification;
use Illuminate\Support\Facades\DB;

/**
 * Handles headteacher approval and decline of class results.
 * When teacher submits scores, they are "Pending Headmaster Approval".
 * Approval is allowed only when all subject_reports for the class are submitted.
 * After decline, teacher can correct scores again (submitted_at cleared).
 */
class HeadteacherApprovalService
{
    /**
     * Check if all subject reports for the class section and term are submitted.
     */
    public function canApproveClassResults(int $classSectionId, int $termId): bool
    {
        $enrollmentIds = ClassSection::findOrFail($classSectionId)
            ->enrollments()
            ->where('school_year_id', Term::findOrFail($termId)->school_year_id)
            ->where('is_active', true)
            ->pluck('id');

        if ($enrollmentIds->isEmpty()) {
            return false;
        }

        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $termId)
            ->pluck('id');

        if ($termReportIds->isEmpty()) {
            return false;
        }

        $totalSubjectReports = \App\Models\SubjectReport::whereIn('term_report_id', $termReportIds)->count();
        $submittedCount = \App\Models\SubjectReport::whereIn('term_report_id', $termReportIds)
            ->whereNotNull('submitted_at')
            ->count();

        return $totalSubjectReports > 0 && $totalSubjectReports === $submittedCount;
    }

    /**
     * Approve all term reports for the class section and term.
     * Sets is_approved_by_headteacher = true and submitted_at = now().
     *
     * @throws \RuntimeException when not all subject reports are submitted
     */
    public function approveClassResults(int $classSectionId, int $termId): int
    {
        if (!$this->canApproveClassResults($classSectionId, $termId)) {
            throw new \RuntimeException('Cannot approve: not all subject reports for this class and term are submitted.');
        }

        $enrollmentIds = ClassSection::findOrFail($classSectionId)
            ->enrollments()
            ->where('school_year_id', Term::findOrFail($termId)->school_year_id)
            ->where('is_active', true)
            ->pluck('id');

        return DB::transaction(function () use ($enrollmentIds, $termId) {
            return TermReport::whereIn('enrollment_id', $enrollmentIds)
                ->where('term_id', $termId)
                ->update([
                    'is_approved_by_headteacher' => true,
                    'submitted_at' => now(),
                ]);
        });
    }

    /**
     * Status for class+term: 'none' | 'pending' | 'approved'
     */
    public function getClassStatus(int $classSectionId, int $termId): string
    {
        $enrollmentIds = ClassSection::findOrFail($classSectionId)
            ->enrollments()
            ->where('school_year_id', Term::findOrFail($termId)->school_year_id)
            ->where('is_active', true)
            ->pluck('id');

        if ($enrollmentIds->isEmpty()) {
            return 'none';
        }

        $termReports = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $termId)
            ->get();

        if ($termReports->isEmpty()) {
            return 'none';
        }

        $allApproved = $termReports->every(fn (TermReport $tr) => $tr->is_approved_by_headteacher);
        if ($allApproved) {
            return 'approved';
        }

        if ($this->canApproveClassResults($classSectionId, $termId)) {
            return 'pending';
        }

        return 'none';
    }

    /**
     * Decline class results: allow teacher to correct scores again.
     * Clears submitted_at on all subject reports and sets is_approved_by_headteacher = false.
     *
     * @return int Number of term reports updated
     */
    public function declineClassResults(int $classSectionId, int $termId): int
    {
        $enrollmentIds = ClassSection::findOrFail($classSectionId)
            ->enrollments()
            ->where('school_year_id', Term::findOrFail($termId)->school_year_id)
            ->where('is_active', true)
            ->pluck('id');

        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $termId)
            ->pluck('id');

        if ($termReportIds->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($termReportIds, $enrollmentIds, $termId) {
            SubjectReport::whereIn('term_report_id', $termReportIds)->update(['submitted_at' => null]);

            return TermReport::whereIn('enrollment_id', $enrollmentIds)
                ->where('term_id', $termId)
                ->update([
                    'is_approved_by_headteacher' => false,
                    'submitted_at' => null,
                ]);
        });
    }

    /**
     * Pending CA submissions for class+term: list of subject IDs with ca_submitted_at set and not yet approved/rejected.
     */
    public function getPendingCaSubmissions(int $classSectionId, int $termId): array
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        if ($termReportIds->isEmpty()) {
            return [];
        }
        $subjectIds = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->whereNotNull('ca_submitted_at')
            ->whereNull('ca_approved_at')
            ->whereNull('ca_rejected_at')
            ->distinct()
            ->pluck('subject_id');
        return Subject::whereIn('id', $subjectIds)->get()->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->toArray();
    }

    /**
     * Pending Exam submissions for class+term.
     */
    public function getPendingExamSubmissions(int $classSectionId, int $termId): array
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        if ($termReportIds->isEmpty()) {
            return [];
        }
        $subjectIds = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->whereNotNull('exam_submitted_at')
            ->whereNull('exam_approved_at')
            ->whereNull('exam_rejected_at')
            ->distinct()
            ->pluck('subject_id');
        return Subject::whereIn('id', $subjectIds)->get()->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->toArray();
    }

    /**
     * Get student names and CA marks for a pending CA submission (for headteacher to view before approving/rejecting).
     *
     * @return array<int, array{student_name: string, ca_mark: float|int|null}>
     */
    public function getPendingCaMarks(int $classSectionId, int $termId, int $subjectId): array
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        if ($termReportIds->isEmpty()) {
            return [];
        }
        return SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->whereNotNull('ca_submitted_at')
            ->whereNull('ca_approved_at')
            ->whereNull('ca_rejected_at')
            ->with(['termReport.enrollment.student'])
            ->get()
            ->map(fn (SubjectReport $sr) => [
                'student_name' => $sr->termReport->enrollment->student
                    ? trim($sr->termReport->enrollment->student->first_name . ' ' . $sr->termReport->enrollment->student->last_name)
                    : '—',
                'ca_mark' => $sr->ca_mark !== null ? (float) $sr->ca_mark : null,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get student names and Exam marks for a pending Exam submission.
     *
     * @return array<int, array{student_name: string, exam_mark: float|int|null}>
     */
    public function getPendingExamMarks(int $classSectionId, int $termId, int $subjectId): array
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        if ($termReportIds->isEmpty()) {
            return [];
        }
        return SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->whereNotNull('exam_submitted_at')
            ->whereNull('exam_approved_at')
            ->whereNull('exam_rejected_at')
            ->with(['termReport.enrollment.student'])
            ->get()
            ->map(fn (SubjectReport $sr) => [
                'student_name' => $sr->termReport->enrollment->student
                    ? trim($sr->termReport->enrollment->student->first_name . ' ' . $sr->termReport->enrollment->student->last_name)
                    : '—',
                'exam_mark' => $sr->exam_mark !== null ? (float) $sr->exam_mark : null,
            ])
            ->values()
            ->toArray();
    }

    private function getTermReportIdsForClass(int $classSectionId, int $termId): \Illuminate\Support\Collection
    {
        $enrollmentIds = ClassSection::findOrFail($classSectionId)
            ->enrollments()
            ->where('school_year_id', Term::findOrFail($termId)->school_year_id)
            ->where('is_active', true)
            ->pluck('id');
        return TermReport::whereIn('enrollment_id', $enrollmentIds)->where('term_id', $termId)->pluck('id');
    }

    /**
     * Approve CA for a subject in class+term. Notifies parents when term report becomes fully approved.
     */
    public function approveCa(int $classSectionId, int $termId, int $subjectId): int
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        $subject = Subject::findOrFail($subjectId);
        $term = Term::with('schoolYear')->findOrFail($termId);
        $classSection = ClassSection::with('schoolClass')->findOrFail($classSectionId);
        $classLabel = $classSection->label . ' (' . ($classSection->schoolClass->name ?? '') . ')';
        $termName = $term->name . ' – ' . ($term->schoolYear->name ?? '');

        $count = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->whereNotNull('ca_submitted_at')
            ->update(['ca_approved_at' => now(), 'ca_rejected_at' => null, 'ca_rejection_reason' => null]);

        $this->checkAndCompleteTermApprovals($termReportIds, $term);
        return $count;
    }

    /**
     * Reject CA for a subject in class+term. Notifies teacher(s) with reason.
     */
    public function rejectCa(int $classSectionId, int $termId, int $subjectId, string $reason): int
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        $subject = Subject::findOrFail($subjectId);
        $term = Term::with('schoolYear')->findOrFail($termId);
        $classSection = ClassSection::with('schoolClass')->findOrFail($classSectionId);
        $classLabel = $classSection->label . ' (' . ($classSection->schoolClass->name ?? '') . ')';
        $termName = $term->name . ' – ' . ($term->schoolYear->name ?? '');

        $count = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->update([
                'ca_submitted_at' => null,
                'ca_rejected_at' => now(),
                'ca_rejection_reason' => $reason,
                'ca_approved_at' => null,
            ]);

        $teacherIds = TeacherAssignment::where('class_section_id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->pluck('teacher_id')
            ->unique();
        $notification = new ReportRejectedNotification('CA', $subject->name, $classLabel, $termName, $reason);
        User::whereIn('id', $teacherIds)->get()->each(fn (User $u) => $u->notify($notification));

        return $count;
    }

    /**
     * Approve Exam for a subject in class+term. Notifies parents when term report becomes fully approved.
     */
    public function approveExam(int $classSectionId, int $termId, int $subjectId): int
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        $term = Term::with('schoolYear')->findOrFail($termId);

        $count = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->whereNotNull('exam_submitted_at')
            ->update(['exam_approved_at' => now(), 'exam_rejected_at' => null, 'exam_rejection_reason' => null]);

        $this->checkAndCompleteTermApprovals($termReportIds, $term);
        return $count;
    }

    /**
     * Reject Exam for a subject in class+term. Notifies teacher(s) with reason.
     */
    public function rejectExam(int $classSectionId, int $termId, int $subjectId, string $reason): int
    {
        $termReportIds = $this->getTermReportIdsForClass($classSectionId, $termId);
        $subject = Subject::findOrFail($subjectId);
        $term = Term::with('schoolYear')->findOrFail($termId);
        $classSection = ClassSection::with('schoolClass')->findOrFail($classSectionId);
        $classLabel = $classSection->label . ' (' . ($classSection->schoolClass->name ?? '') . ')';
        $termName = $term->name . ' – ' . ($term->schoolYear->name ?? '');

        $count = SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $subjectId)
            ->update([
                'exam_submitted_at' => null,
                'exam_rejected_at' => now(),
                'exam_rejection_reason' => $reason,
                'exam_approved_at' => null,
            ]);

        $teacherIds = TeacherAssignment::where('class_section_id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->pluck('teacher_id')
            ->unique();
        $notification = new ReportRejectedNotification('Exam', $subject->name, $classLabel, $termName, $reason);
        User::whereIn('id', $teacherIds)->get()->each(fn (User $u) => $u->notify($notification));

        return $count;
    }

    /**
     * If any term report is now fully approved, set is_approved_by_headteacher and notify parents.
     */
    private function checkAndCompleteTermApprovals(\Illuminate\Support\Collection $termReportIds, Term $term): void
    {
        $this->ensureApprovalStatusForTermReports($termReportIds, $term);
    }

    /**
     * For the given term report IDs, set is_approved_by_headteacher and notify parents when a term report
     * is fully approved (all subject reports have CA/Exam either not entered or approved).
     * Call this after generating reports so parent visibility is correct.
     * Only updates and notifies when not already approved (avoids duplicate notifications).
     */
    public function ensureApprovalStatusForTermReports(\Illuminate\Support\Collection $termReportIds, Term $term): void
    {
        if ($termReportIds->isEmpty()) {
            return;
        }
        $termName = $term->name . ' – ' . ($term->schoolYear->name ?? '');
        TermReport::whereIn('id', $termReportIds)
            ->with(['enrollment.student.parents.user', 'subjectReports'])
            ->get()
            ->each(function (TermReport $tr) use ($termName, $term) {
                if (!$tr->isFullyApproved()) {
                    return;
                }
                if ($tr->is_approved_by_headteacher) {
                    return;
                }
                $tr->update(['is_approved_by_headteacher' => true, 'submitted_at' => now()]);
                $student = $tr->enrollment->student;
                $studentName = $student->first_name . ' ' . $student->last_name;
                $notification = new ReportApprovedNotification($studentName, $termName, $student->id, $term->id);
                $student->parents->each(function ($parent) use ($notification) {
                    if ($parent->user) {
                        $parent->user->notify($notification);
                    }
                });
            });
    }
}
