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
     * Authorization: parent can only view own children.
     * Parents: can only view when the term report is approved by headmaster (then they can see and download).
     */
    public function show(Request $request, Student $student, Term $term): View|string
    {
        $this->authorize('viewReportCard', $student);

        $enrollment = $student->enrollments()
            ->where('school_year_id', $term->school_year_id)
            ->where('is_active', true)
            ->with(['classSection.schoolClass', 'termReports' => fn ($q) => $q->where('term_id', $term->id)->with(['subjectReports.subject', 'behaviourRatings'])])
            ->firstOrFail();

        $termReport = $enrollment->termReports->first();
        if (!$termReport) {
            abort(404, 'No term report found for this student and term.');
        }

        // Parents: can view when headteacher has approved at least one CA/Exam (interim), or full report when fully approved
        $isParent = auth()->user()->isParent();
        if ($isParent) {
            if (!$termReport->is_approved_by_headteacher && !$termReport->hasAnyApprovedMarks()) {
                abort(403, 'No approved results yet. Marks will appear here as the headteacher approves each subject.');
            }
        }

        $attendanceSummary = $this->getAttendanceSummary($enrollment->id, $term->id);
        $subjectReports = $termReport->subjectReports->sortBy(fn ($sr) => $sr->subject->name);
        $showFullReport = !$isParent || $termReport->is_approved_by_headteacher;

        return view('report-cards.show', [
            'student' => $student,
            'term' => $term->load('schoolYear'),
            'enrollment' => $enrollment,
            'termReport' => $termReport,
            'subjectReports' => $subjectReports,
            'behaviourRatings' => $termReport->behaviourRatings,
            'attendanceSummary' => $attendanceSummary,
            'showFullReport' => $showFullReport,
        ]);
    }

    /**
     * Download report card as PDF.
     * Parents: can only download when the term report is approved by headmaster.
     */
    public function downloadPdf(Student $student, Term $term)
    {
        $this->authorize('viewReportCard', $student);

        $enrollment = $student->enrollments()
            ->where('school_year_id', $term->school_year_id)
            ->where('is_active', true)
            ->with(['classSection.schoolClass', 'termReports' => fn ($q) => $q->where('term_id', $term->id)->with(['subjectReports.subject', 'behaviourRatings'])])
            ->firstOrFail();

        $termReport = $enrollment->termReports->first();
        if (!$termReport) {
            abort(404, 'No term report found for this student and term.');
        }

        if (auth()->user()->isParent() && !$termReport->is_approved_by_headteacher) {
            abort(403, 'This report card is not yet approved. It will be available for download after headmaster approval.');
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
