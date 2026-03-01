<?php

namespace App\Livewire\Teacher;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\SubjectReport;
use App\Models\Term;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MarksEntry extends Component
{
    public $classSections = [];
    public $subjects = [];
    public $terms = [];

    public $selectedClassSection = null;
    public $selectedSubject = null;
    public $selectedTerm = null;

    public $marks = [];

    /** Which marks to enter: 'ca' (Continuous Assessment, out of 30) or 'exam' (out of 70). */
    public $markEntryType = 'ca';

    /** Whether the current class+term has been submitted for approval (scores read-only). */
    public $isSubmitted = false;

    public function mount(): void
    {
        $teacherId = auth()->id();

        // Load class sections assigned to this teacher
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

        // Load current school year terms (active term first for entry; others for view past)
        $termsCollection = Term::whereHas('schoolYear', fn ($q) => $q->where('is_current', true))
            ->with('schoolYear')
            ->orderBy('number')
            ->get();
        $activeTermId = static::getActiveTermId();
        $termsCollection = $termsCollection->sortByDesc(fn ($t) => $t->id === $activeTermId ? 1 : 0)->values();
        $this->terms = $termsCollection->map(function ($term) use ($activeTermId) {
            $label = $term->name . ' – ' . ($term->schoolYear->name ?? '');
            $label .= $term->id === $activeTermId ? ' (active – enter marks)' : ' (past – read only)';
            return ['id' => $term->id, 'label' => $label];
        })->toArray();

        // Pre-select from query params (e.g. from Result Review "Marks entry" link)
        $classSection = request()->query('class_section');
        $term = request()->query('term');
        $subject = request()->query('subject');
        if ($classSection && $term && $subject) {
            $this->selectedClassSection = $classSection;
            $this->selectedTerm = $term;
            $this->loadSubjects();
            $this->selectedSubject = $subject;
            $this->loadStudents();
        }
    }

    public function updatedSelectedClassSection($value): void
    {
        $this->selectedClassSection = $value;
        $this->loadSubjects();
        $this->loadStudents();
    }

    public function updatedSelectedSubject($value): void
    {
        $this->selectedSubject = $value;
        $this->loadStudents();
    }

    public function updatedSelectedTerm($value): void
    {
        $this->selectedTerm = $value;
        $this->loadStudents();
    }

    protected function loadSubjects(): void
    {
        $this->subjects = [];

        if (!$this->selectedClassSection) {
            return;
        }

        $teacherId = auth()->id();

        $this->subjects = DB::table('teacher_assignments')
            ->where('teacher_id', $teacherId)
            ->where('class_section_id', $this->selectedClassSection)
            ->join('subjects', 'teacher_assignments.subject_id', '=', 'subjects.id')
            ->select('subjects.id', 'subjects.name', 'subjects.code')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'label' => "{$subject->name} ({$subject->code})",
                ];
            })
            ->toArray();
    }

    public function loadStudents(): void
    {
        if (!$this->selectedClassSection || !$this->selectedSubject || !$this->selectedTerm) {
            $this->marks = [];
            return;
        }

        $enrollments = Enrollment::where('class_section_id', $this->selectedClassSection)
            ->where('is_active', true)
            ->with(['student', 'termReports' => function ($query) {
                $query->where('term_id', $this->selectedTerm);
            }])
            ->get();

        $enrollmentIds = $enrollments->pluck('id')->toArray();
        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $this->selectedTerm)
            ->pluck('id');
        $termApproved = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $this->selectedTerm)
            ->where('is_approved_by_headteacher', true)
            ->exists();
        $this->isSubmitted = $termApproved;

        $this->marks = $enrollments->map(function ($enrollment) use ($termApproved) {
            $termReport = $enrollment->termReports->first();
            $subjectReport = null;

            if ($termReport) {
                $subjectReport = SubjectReport::where('term_report_id', $termReport->id)
                    ->where('subject_id', $this->selectedSubject)
                    ->first();
            }

            $canEditCa = $subjectReport ? $subjectReport->canEditCa($termApproved) : !$termApproved;
            $canEditExam = $subjectReport ? $subjectReport->canEditExam($termApproved) : !$termApproved;

            return [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name,
                'admission_number' => $enrollment->student->admission_number,
                'ca_mark' => $subjectReport?->ca_mark ?? '',
                'exam_mark' => $subjectReport?->exam_mark ?? '',
                'total_mark' => $subjectReport?->total_mark ?? '',
                'grade' => $subjectReport?->grade ?? '',
                'remark' => $subjectReport?->remark ?? '',
                'teacher_comment' => $subjectReport?->teacher_comment ?? '',
                'term_report_id' => $termReport?->id,
                'subject_report_id' => $subjectReport?->id,
                'can_edit_ca' => $canEditCa,
                'can_edit_exam' => $canEditExam,
                'ca_rejection_reason' => $subjectReport?->ca_rejection_reason,
                'exam_rejection_reason' => $subjectReport?->exam_rejection_reason,
            ];
        })->toArray();
    }

    public function updatedMarks($value, $key): void
    {
        if (str_contains($key, 'ca_mark') || str_contains($key, 'exam_mark')) {
            $parts = explode('.', $key);
            // Key may be "0.ca_mark" (2 parts) or "marks.0.ca_mark" (3 parts). Row index is the numeric segment.
            $index = count($parts) === 3 ? (int) $parts[1] : (int) $parts[0];

            if (isset($this->marks[$index])) {
                $caMark = (float) ($this->marks[$index]['ca_mark'] ?? 0);
                $examMark = (float) ($this->marks[$index]['exam_mark'] ?? 0);

                // Enforce limits: CA max 30, Exam max 70
                if ($caMark > 30) {
                    $this->marks[$index]['ca_mark'] = 30;
                    $caMark = 30;
                }
                if ($caMark < 0) {
                    $this->marks[$index]['ca_mark'] = 0;
                    $caMark = 0;
                }
                if ($examMark > 70) {
                    $this->marks[$index]['exam_mark'] = 70;
                    $examMark = 70;
                }
                if ($examMark < 0) {
                    $this->marks[$index]['exam_mark'] = 0;
                    $examMark = 0;
                }

                // Only set total and grade when BOTH CA and Exam are present (teacher may enter only one)
                $caPresent = ($this->marks[$index]['ca_mark'] ?? '') !== '' && ($this->marks[$index]['ca_mark'] ?? null) !== null;
                $examPresent = ($this->marks[$index]['exam_mark'] ?? '') !== '' && ($this->marks[$index]['exam_mark'] ?? null) !== null;
                if ($caPresent && $examPresent) {
                    $totalMark = $caMark + $examMark;
                    $this->marks[$index]['total_mark'] = round($totalMark, 2);
                    $gradeInfo = \App\Models\GradingScale::getGradeForMark($totalMark);
                    if ($gradeInfo) {
                        $this->marks[$index]['grade'] = $gradeInfo['grade'];
                        $this->marks[$index]['remark'] = $gradeInfo['remark'];
                    }
                } else {
                    $this->marks[$index]['total_mark'] = '';
                    $this->marks[$index]['grade'] = '';
                    $this->marks[$index]['remark'] = '';
                }
            }
        }
    }

    /**
     * Validate current marks: CA must be 0–30, Exam must be 0–70 when present.
     * Returns list of error messages (empty if valid).
     */
    public function getValidationErrors(): array
    {
        $errors = [];
        foreach ($this->marks as $index => $mark) {
            $name = $mark['student_name'] ?? "Row " . ($index + 1);
            $ca = $mark['ca_mark'] ?? '';
            $exam = $mark['exam_mark'] ?? '';
            if ($ca !== '' && $ca !== null) {
                $num = (float) $ca;
                if ($num < 0 || $num > 30) {
                    $errors[] = "CA for {$name} must be between 0 and 30 (got " . round($num, 2) . ").";
                }
            }
            if ($exam !== '' && $exam !== null) {
                $num = (float) $exam;
                if ($num < 0 || $num > 70) {
                    $errors[] = "Exam for {$name} must be between 0 and 70 (got " . round($num, 2) . ").";
                }
            }
        }
        return $errors;
    }

    /**
     * Review summary for selected class + term: all subjects with entered/total counts and status.
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
     * Save scores as draft. Validates CA (0–30) and Exam (0–70). Wrong data is not saved.
     */
    public function saveAsDraft(): void
    {
        $errors = $this->getValidationErrors();
        if (!empty($errors)) {
            session()->flash('error', 'Draft not saved. Fix the following: ' . implode(' ', $errors));
            return;
        }
        $this->saveMarks(submit: false);
    }

    /**
     * Save scores (same as draft). Validates CA (0–30) and Exam (0–70). Wrong data is not saved.
     */
    public function save(): void
    {
        $errors = $this->getValidationErrors();
        if (!empty($errors)) {
            session()->flash('error', 'Marks not saved. Fix the following: ' . implode(' ', $errors));
            return;
        }
        $this->saveMarks(submit: false);
    }

    /**
     * Submit CA marks only for head teacher approval. Validates CA 0–30.
     */
    public function submitCaForApproval(): void
    {
        $errors = $this->getValidationErrorsForCa();
        if (!empty($errors)) {
            session()->flash('error', 'Cannot submit CA. ' . implode(' ', $errors));
            return;
        }
        $this->saveMarks(submit: false); // ensure data is saved
        $enrollmentIds = Enrollment::where('class_section_id', $this->selectedClassSection)
            ->where('is_active', true)
            ->pluck('id');
        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $this->selectedTerm)
            ->pluck('id');
        SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $this->selectedSubject)
            ->whereNotNull('ca_mark')
            ->update([
                'ca_submitted_at' => now(),
                'ca_rejected_at' => null,
                'ca_rejection_reason' => null,
            ]);
        session()->flash('success', 'CA marks submitted for head teacher approval. You can still enter or edit Exam marks.');
        $this->loadStudents();
    }

    /**
     * Submit Exam marks only for head teacher approval. Validates Exam 0–70.
     */
    public function submitExamForApproval(): void
    {
        $errors = $this->getValidationErrorsForExam();
        if (!empty($errors)) {
            session()->flash('error', 'Cannot submit Exam. ' . implode(' ', $errors));
            return;
        }
        $this->saveMarks(submit: false);
        $enrollmentIds = Enrollment::where('class_section_id', $this->selectedClassSection)
            ->where('is_active', true)
            ->pluck('id');
        $termReportIds = TermReport::whereIn('enrollment_id', $enrollmentIds)
            ->where('term_id', $this->selectedTerm)
            ->pluck('id');
        SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $this->selectedSubject)
            ->whereNotNull('exam_mark')
            ->update([
                'exam_submitted_at' => now(),
                'exam_rejected_at' => null,
                'exam_rejection_reason' => null,
            ]);
        session()->flash('success', 'Exam marks submitted for head teacher approval. You can still enter or edit CA marks.');
        $this->loadStudents();
    }

    /** Validation errors for CA only (0–30). */
    public function getValidationErrorsForCa(): array
    {
        $errors = [];
        foreach ($this->marks as $index => $mark) {
            $ca = $mark['ca_mark'] ?? '';
            if ($ca === '' || $ca === null) {
                continue;
            }
            $num = (float) $ca;
            if ($num < 0 || $num > 30) {
                $name = $mark['student_name'] ?? 'Row ' . ($index + 1);
                $errors[] = "CA for {$name} must be 0–30.";
            }
        }
        return $errors;
    }

    /** Validation errors for Exam only (0–70). */
    public function getValidationErrorsForExam(): array
    {
        $errors = [];
        foreach ($this->marks as $index => $mark) {
            $exam = $mark['exam_mark'] ?? '';
            if ($exam === '' || $exam === null) {
                continue;
            }
            $num = (float) $exam;
            if ($num < 0 || $num > 70) {
                $name = $mark['student_name'] ?? 'Row ' . ($index + 1);
                $errors[] = "Exam for {$name} must be 0–70.";
            }
        }
        return $errors;
    }

    /**
     * Save CA and exam scores. When $submit is true, marks only this subject's scores as submitted (not the whole term).
     * Validates CA ≤ 30 and Exam ≤ 70 before saving.
     */
    protected function saveMarks(bool $submit = false): void
    {
        if (!$this->selectedClassSection || !$this->selectedSubject || !$this->selectedTerm) {
            session()->flash('error', 'Please select class section, subject, and term.');
            return;
        }

        $errors = $this->getValidationErrors();
        if (!empty($errors)) {
            session()->flash('error', 'Cannot save: invalid marks. ' . implode(' ', $errors));
            return;
        }

        if ($this->isSubmitted) {
            session()->flash('error', 'Scores for this subject are already submitted. Editing is not allowed.');
            return;
        }

        $activeTermId = static::getActiveTermId();
        if (!$activeTermId || (string) $this->selectedTerm !== (string) $activeTermId) {
            session()->flash('error', 'You can enter or edit marks only for the active term. Select the active term or view past terms as read-only.');
            return;
        }

        DB::transaction(function () use ($submit) {
            foreach ($this->marks as $markData) {
                if (empty($markData['ca_mark']) && empty($markData['exam_mark'])) {
                    continue;
                }

                $termReport = TermReport::firstOrCreate(
                    [
                        'enrollment_id' => $markData['enrollment_id'],
                        'term_id' => $this->selectedTerm,
                    ]
                );

                $data = [
                    'ca_mark' => $markData['ca_mark'] ?? null,
                    'exam_mark' => $markData['exam_mark'] ?? null,
                    'teacher_comment' => $markData['teacher_comment'] ?? null,
                ];
                if ($submit) {
                    $data['submitted_at'] = now();
                }
                $subjectReport = SubjectReport::updateOrCreate(
                    [
                        'term_report_id' => $termReport->id,
                        'subject_id' => $this->selectedSubject,
                    ],
                    $data
                );

                $subjectReport->calculateTotal();
                $subjectReport->save();
            }
        });

        if ($submit) {
            session()->flash('success', 'Scores saved and submitted for head teacher approval. Parents will be able to view report cards after the headmaster approves.');
        } else {
            session()->flash('success', 'Draft saved successfully. Marks are validated (CA 0–30, Exam 0–70). You can edit and submit for approval when ready.');
        }
        $this->loadStudents();
    }

    /**
     * Active term for data entry: term with is_active = true, or first term of current school year as fallback.
     */
    protected static function getActiveTermId(): ?int
    {
        $active = Term::where('is_active', true)->value('id');
        if ($active !== null) {
            return (int) $active;
        }
        $firstTerm = Term::whereHas('schoolYear', fn ($q) => $q->where('is_current', true))
            ->orderBy('number')
            ->first();
        return $firstTerm ? (int) $firstTerm->id : null;
    }

    public function render()
    {
        $gradingScales = GradingScale::orderBy('min_mark', 'desc')->get();
        $activeTermId = static::getActiveTermId();
        $canEdit = !$this->isSubmitted
            && $activeTermId
            && (string) $this->selectedTerm === (string) $activeTermId;

        return view('livewire.teacher.marks-entry', [
            'gradingScales' => $gradingScales,
            'activeTermId' => $activeTermId,
            'canEdit' => $canEdit,
            'validationErrors' => $this->getValidationErrors(),
        ])->layout('layouts.dashboard', [
            'headerTitle' => 'Marks entry',
            'headerSubtitle' => 'Enter and manage subject marks by class and term',
        ]);
    }
}

