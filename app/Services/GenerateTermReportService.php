<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\Term;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;

/**
 * Generates and updates term reports for a class section.
 * Idempotent: running multiple times does not duplicate data.
 */
class GenerateTermReportService
{
    /**
     * Generate complete term reports for a class section.
     *
     * @param int $classSectionId
     * @param int $termId
     * @return array{generated: int, updated: int}
     */
    public function generate(int $classSectionId, int $termId): array
    {
        $term = Term::findOrFail($termId);
        $classSection = ClassSection::findOrFail($classSectionId);

        $enrollments = $classSection->enrollments()
            ->where('school_year_id', $term->school_year_id)
            ->where('is_active', true)
            ->with(['student'])
            ->get();

        if ($enrollments->isEmpty()) {
            return ['generated' => 0, 'updated' => 0];
        }

        return DB::transaction(function () use ($enrollments, $term) {
            $generated = 0;
            $updated = 0;
            $termReports = [];

            // Step 1 & 2: Create/update term reports and compute per-student average
            foreach ($enrollments as $enrollment) {
                $termReport = TermReport::firstOrCreate(
                    [
                        'enrollment_id' => $enrollment->id,
                        'term_id' => $term->id,
                    ],
                    ['average' => null, 'position' => null, 'class_size' => null, 'class_average' => null]
                );
                if ($termReport->wasRecentlyCreated) {
                    $generated++;
                } else {
                    $updated++;
                }

                $termReport->load('subjectReports');
                $totals = $termReport->subjectReports->pluck('total_mark')->filter(fn ($v) => $v !== null);
                $average = $totals->isEmpty() ? null : round($totals->avg(), 2);
                $termReport->update(['average' => $average]);
                $termReports[] = $termReport;
            }

            // Step 3: Class statistics (class_size, class_average)
            $classSize = count($termReports);
            $averages = collect($termReports)->pluck('average')->filter(fn ($v) => $v !== null)->values();
            $classAverage = $averages->isEmpty() ? null : round($averages->avg(), 2);

            foreach ($termReports as $tr) {
                $tr->update([
                    'class_size' => $classSize,
                    'class_average' => $classAverage,
                ]);
            }

            // Step 4: Position ranking (ties get same position, next rank skips: 90, 90, 85 -> 1, 1, 3)
            $this->assignPositions($termReports);

            // Step 5: If any term report is fully approved (all CA/Exam approved), set is_approved_by_headteacher
            // so parents can view report cards. Idempotent: only updates when not already approved.
            $termReportIds = collect($termReports)->pluck('id');
            app(HeadteacherApprovalService::class)->ensureApprovalStatusForTermReports($termReportIds, $term);

            return ['generated' => $generated, 'updated' => $updated];
        });
    }

    /**
     * Assign position ranking. Ties get same position; next rank skips.
     *
     * @param iterable<TermReport> $termReports
     */
    private function assignPositions(iterable $termReports): void
    {
        $sorted = collect($termReports)->sortByDesc('average')->values();
        $position = 1;
        $prevAverage = null;
        $sameRankCount = 0;

        foreach ($sorted as $tr) {
            $avg = $tr->average;
            if ($avg === null) {
                $tr->update(['position' => null]);
                continue;
            }
            if ($prevAverage !== null && (float) $avg !== (float) $prevAverage) {
                $position += $sameRankCount;
                $sameRankCount = 0;
            }
            $tr->update(['position' => $position]);
            $prevAverage = $avg;
            $sameRankCount++;
        }
    }
}
