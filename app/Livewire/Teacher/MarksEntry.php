<?php

namespace App\Livewire\Teacher;

use App\Models\ClassSection;
use App\Models\Enrollment;
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

        // Load current school year terms
        $this->terms = Term::whereHas('schoolYear', fn ($q) =>
                $q->where('is_current', true)
            )
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

    public function saveMarks(): void
    {
        if (!$this->selectedClassSection || !$this->selectedSubject || !$this->selectedTerm) {
            session()->flash('error', 'Please select class section, subject, and term.');
            return;
        }

        DB::transaction(function () {
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

                $subjectReport = SubjectReport::updateOrCreate(
                    [
                        'term_report_id' => $termReport->id,
                        'subject_id' => $this->selectedSubject,
                    ],
                    [
                        'ca_mark' => $markData['ca_mark'] ?? null,
                        'exam_mark' => $markData['exam_mark'] ?? null,
                        'teacher_comment' => $markData['teacher_comment'] ?? null,
                    ]
                );

                $subjectReport->calculateTotal();
                $subjectReport->save();
            }
        });

        session()->flash('success', 'Marks saved successfully!');
        $this->loadStudents();
    }

    public function render()
    {
        return view('livewire.teacher.marks-entry')
            ->layout('layouts.teacher', [
                'headerTitle' => 'Marks entry',
                'headerSubtitle' => 'Enter and manage subject marks by class and term',
            ]);
    }
}

