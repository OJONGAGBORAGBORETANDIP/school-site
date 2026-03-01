<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\SubjectReport;
use App\Models\Term;
use App\Models\TermReport;
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
}
