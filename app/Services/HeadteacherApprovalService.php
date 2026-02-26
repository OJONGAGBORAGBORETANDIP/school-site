<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\Term;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;

/**
 * Handles headteacher approval of class results.
 * Approval is allowed only when all subject_reports for the class are submitted.
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
}
