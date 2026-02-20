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
        $activeTermId = Term::where('is_active', true)->value('id');
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
        $this->isSubmitted = $termReportIds->isNotEmpty() && SubjectReport::whereIn('term_report_id', $termReportIds)
            ->where('subject_id', $this->selectedSubject)
            ->whereNotNull('submitted_at')
            ->exists();

        $this->marks = $enrollments->map(function ($enrollment) {
            $termReport = $enrollment->termReports->first();
            $subjectReport = null;

            if ($termReport) {
                $subjectReport = SubjectReport::where('term_report_id', $termReport->id)
                    ->where('subject_id', $this->selectedSubject)
                    ->first();
            }

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
            ];
        })->toArray();
    }

    public function updatedMarks($value, $key): void
    {
        if (str_contains($key, 'ca_mark') || str_contains($key, 'exam_mark')) {
            $parts = explode('.', $key);
            $index = (int) $parts[1];

            if (isset($this->marks[$index])) {
                $caMark = (float) ($this->marks[$index]['ca_mark'] ?? 0);
                $examMark = (float) ($this->marks[$index]['exam_mark'] ?? 0);

                if ($caMark > 0 || $examMark > 0) {
                    $totalMark = ($caMark * 0.4) + ($examMark * 0.6);
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
     * Validate current marks: CA and Exam must be 0–100 when present.
     * Returns list of error messages (empty if valid).
     */
    public function getValidationErrors(): array
    {
        $errors = [];
        foreach ($this->marks as $index => $mark) {
            $name = $mark['student_name'] ?? "Row " . ($index + 1);
            foreach (['ca_mark' => 'CA', 'exam_mark' => 'Exam'] as $key => $label) {
                $val = $mark[$key] ?? '';
                if ($val === '' || $val === null) {
                    continue;
                }
                $num = (float) $val;
                if ($num < 0 || $num > 100) {
                    $errors[] = "{$label} for {$name} must be between 0 and 100 (got " . round($num, 2) . ").";
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
     * Save scores as draft. Teacher can still edit before submitting.
     */
    public function saveAsDraft(): void
    {
        $this->saveMarks(submit: false);
    }

    /**
     * Save scores and submit for approval by head teacher. Scores become read-only.
     * Validates accuracy of marks before allowing submit.
     */
    public function submitForApproval(): void
    {
        $errors = $this->getValidationErrors();
        if (!empty($errors)) {
            session()->flash('error', 'Please fix the following before submitting: ' . implode(' ', $errors));
            return;
        }
        $this->saveMarks(submit: true);
    }

    /**
     * Save CA and exam scores. When $submit is true, marks only this subject's scores as submitted (not the whole term).
     */
    protected function saveMarks(bool $submit = false): void
    {
        if (!$this->selectedClassSection || !$this->selectedSubject || !$this->selectedTerm) {
            session()->flash('error', 'Please select class section, subject, and term.');
            return;
        }

        if ($this->isSubmitted) {
            session()->flash('error', 'Scores for this subject are already submitted. Editing is not allowed.');
            return;
        }

        $activeTermId = Term::where('is_active', true)->value('id');
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
            session()->flash('success', 'Scores for this subject saved and submitted for head teacher approval. You can still enter and submit other subjects.');
        } else {
            session()->flash('success', 'Scores saved as draft. You can edit and submit later.');
        }
        $this->loadStudents();
    }

    public function render()
    {
        $gradingScales = GradingScale::orderBy('min_mark', 'desc')->get();
        $activeTermId = Term::where('is_active', true)->value('id');
        $canEdit = !$this->isSubmitted
            && $activeTermId
            && (string) $this->selectedTerm === (string) $activeTermId;

        return view('livewire.teacher.marks-entry', [
            'gradingScales' => $gradingScales,
            'activeTermId' => $activeTermId,
            'canEdit' => $canEdit,
        ])->layout('layouts.dashboard', [
            'headerTitle' => 'Marks entry',
            'headerSubtitle' => 'Enter and manage subject marks by class and term',
        ]);
    }
}

