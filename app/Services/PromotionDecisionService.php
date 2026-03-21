<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\PromotionDecision;
use App\Models\SchoolYear;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;

/**
 * Computes and stores promotion decisions per enrollment for a school year.
 * Default logic (primary /20 scale): average >= 10 → Promoted, average < 10 → Repeat.
 */
class PromotionDecisionService
{
    /** Promotion threshold: average >= this value means promoted. */
    public const PROMOTION_THRESHOLD = 10;

    /**
     * Generate promotion decisions for a school year based on final term results.
     * Uses the last term of the school year (or the one with most term_reports) for average.
     *
     * @param int $schoolYearId
     * @return int Number of decisions created/updated
     */
    public function generateForSchoolYear(int $schoolYearId): int
    {
        $schoolYear = SchoolYear::findOrFail($schoolYearId);
        $terms = $schoolYear->terms()->orderBy('number', 'desc')->get();

        if ($terms->isEmpty()) {
            return 0;
        }

        // Use last term for promotion decision
        $lastTermId = $terms->first()->id;
        $enrollmentIds = TermReport::where('term_id', $lastTermId)->pluck('enrollment_id')->unique();

        return DB::transaction(function () use ($enrollmentIds, $schoolYearId, $lastTermId) {
            $count = 0;
            foreach ($enrollmentIds as $enrollmentId) {
                $enrollment = Enrollment::with('classSection.schoolClass')->find($enrollmentId);
                if (!$enrollment) {
                    continue;
                }

                $termReport = TermReport::where('enrollment_id', $enrollmentId)
                    ->where('term_id', $lastTermId)
                    ->first();

                if (!$termReport || $termReport->average === null) {
                    continue;
                }

                $average = (float) $termReport->average;
                $isPromoted = $average >= self::PROMOTION_THRESHOLD;
                $nextClassLabel = null;
                if ($isPromoted && $enrollment->classSection && $enrollment->classSection->schoolClass) {
                    $nextLevel = $enrollment->classSection->schoolClass->level + 1;
                    $nextClassLabel = "Primary {$nextLevel}";
                }

                PromotionDecision::updateOrCreate(
                    [
                        'enrollment_id' => $enrollmentId,
                        'school_year_id' => $schoolYearId,
                    ],
                    [
                        'is_promoted' => $isPromoted,
                        'next_class_label' => $nextClassLabel,
                        'decision_note' => $isPromoted ? 'Promoted' : 'Repeat',
                    ]
                );
                $count++;
            }

            return $count;
        });
    }

    /**
     * Generate promotion decision for a single enrollment based on term report average.
     */
    public function generateForEnrollment(int $enrollmentId, int $schoolYearId): PromotionDecision
    {
        $enrollment = Enrollment::with('classSection.schoolClass')->findOrFail($enrollmentId);
        $termReport = TermReport::where('enrollment_id', $enrollmentId)
            ->whereHas('term', fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->orderByDesc('average')
            ->first();

        $average = $termReport ? (float) $termReport->average : 0;
        $isPromoted = $average >= self::PROMOTION_THRESHOLD;
        $nextClassLabel = null;
        if ($isPromoted && $enrollment->classSection && $enrollment->classSection->schoolClass) {
            $nextLevel = $enrollment->classSection->schoolClass->level + 1;
            $nextClassLabel = "Primary {$nextLevel}";
        }

        return PromotionDecision::updateOrCreate(
            [
                'enrollment_id' => $enrollmentId,
                'school_year_id' => $schoolYearId,
            ],
            [
                'is_promoted' => $isPromoted,
                'next_class_label' => $nextClassLabel,
                'decision_note' => $isPromoted ? 'Promoted' : 'Repeat',
            ]
        );
    }
}
