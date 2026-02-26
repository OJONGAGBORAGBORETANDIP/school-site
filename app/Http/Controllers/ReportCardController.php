<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Term;
use App\Services\PublishResultsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Displays and exports report cards for a student and term.
 * Parents can only view report cards of their own children when results are published.
 */
class ReportCardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PublishResultsService $publishResultsService
    ) {}

    /**
     * Show report card for a student and term.
     * Authorization: parent can only view own children; results must be published (or user is staff).
     */
    public function show(Request $request, Student $student, Term $term): View|string
    {
        $this->authorize('viewReportCard', $student);

        // Parents: cannot access unpublished results
        if (auth()->user()->isParent() && !$this->publishResultsService->isPublished($term->id)) {
            abort(403, 'Results for this term are not yet published.');
        }

        $enrollment = $student->enrollments()
            ->where('school_year_id', $term->school_year_id)
            ->where('is_active', true)
            ->with(['classSection.schoolClass', 'termReports' => fn ($q) => $q->where('term_id', $term->id)->with(['subjectReports.subject', 'behaviourRatings'])])
            ->firstOrFail();

        $termReport = $enrollment->termReports->first();
        if (!$termReport) {
            abort(404, 'No term report found for this student and term.');
        }

        $attendanceSummary = $this->getAttendanceSummary($enrollment->id, $term->id);

        return view('report-cards.show', [
            'student' => $student,
            'term' => $term->load('schoolYear'),
            'enrollment' => $enrollment,
            'termReport' => $termReport,
            'subjectReports' => $termReport->subjectReports->sortBy(fn ($sr) => $sr->subject->name),
            'behaviourRatings' => $termReport->behaviourRatings,
            'attendanceSummary' => $attendanceSummary,
        ]);
    }

    /**
     * Download report card as PDF.
     */
    public function downloadPdf(Student $student, Term $term)
    {
        $this->authorize('viewReportCard', $student);

        if (auth()->user()->isParent() && !$this->publishResultsService->isPublished($term->id)) {
            abort(403, 'Results for this term are not yet published.');
        }

        $enrollment = $student->enrollments()
            ->where('school_year_id', $term->school_year_id)
            ->where('is_active', true)
            ->with(['classSection.schoolClass', 'termReports' => fn ($q) => $q->where('term_id', $term->id)->with(['subjectReports.subject', 'behaviourRatings'])])
            ->firstOrFail();

        $termReport = $enrollment->termReports->first();
        if (!$termReport) {
            abort(404, 'No term report found for this student and term.');
        }

        $attendanceSummary = $this->getAttendanceSummary($enrollment->id, $term->id);
        $subjectReports = $termReport->subjectReports->sortBy(fn ($sr) => $sr->subject->name);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report-card', [
            'student' => $student,
            'term' => $term->load('schoolYear'),
            'enrollment' => $enrollment,
            'termReport' => $termReport,
            'subjectReports' => $subjectReports,
            'behaviourRatings' => $termReport->behaviourRatings,
            'attendanceSummary' => $attendanceSummary,
        ]);

        $filename = sprintf('report-card-%s-term-%s.pdf', $student->admission_number, $term->number);

        return $pdf->download($filename);
    }

    /**
     * Get attendance summary for enrollment and term: total present, absent, late.
     *
     * @return array{present: int, absent: int, late: int}
     */
    private function getAttendanceSummary(int $enrollmentId, int $termId): array
    {
        $counts = Attendance::where('enrollment_id', $enrollmentId)
            ->where('term_id', $termId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'present' => (int) ($counts['present'] ?? 0),
            'absent' => (int) ($counts['absent'] ?? 0),
            'late' => (int) ($counts['late'] ?? 0),
        ];
    }
}
