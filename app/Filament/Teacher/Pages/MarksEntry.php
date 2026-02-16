<?php

namespace App\Filament\Teacher\Pages;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\SubjectReport;
use App\Models\Term;
use App\Models\TermReport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MarksEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.teacher.pages.marks-entry';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-pencil-square';
    }

    public static function getNavigationLabel(): string
    {
        return 'Marks Entry';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public ?array $data = [];

    public $selectedClassSection = null;
    public $selectedSubject = null;
    public $selectedTerm = null;
    public $marks = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $teacherId = auth()->id();

        return $form
            ->schema([
                Select::make('class_section_id')
                    ->label('Class Section')
                    ->options(function () use ($teacherId) {
                        return ClassSection::whereHas('teacherAssignments', function ($query) use ($teacherId) {
                            $query->where('teacher_id', $teacherId);
                        })
                        ->with('schoolClass')
                        ->get()
                        ->mapWithKeys(function ($section) {
                            return [$section->id => $section->label . ' - ' . $section->schoolClass->name];
                        });
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedClassSection = $state;
                        $this->loadStudents();
                    })
                    ->placeholder('Select a class section'),

                Select::make('subject_id')
                    ->label('Subject')
                    ->options(function () use ($teacherId) {
                        if (!$this->selectedClassSection) {
                            return [];
                        }

                        return DB::table('teacher_assignments')
                            ->where('teacher_id', $teacherId)
                            ->where('class_section_id', $this->selectedClassSection)
                            ->join('subjects', 'teacher_assignments.subject_id', '=', 'subjects.id')
                            ->select('subjects.id', 'subjects.name', 'subjects.code')
                            ->get()
                            ->mapWithKeys(function ($subject) {
                                return [$subject->id => $subject->name . ' (' . $subject->code . ')'];
                            });
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedSubject = $state;
                        $this->loadStudents();
                    })
                    ->placeholder('Select a subject')
                    ->disabled(fn () => !$this->selectedClassSection),

                Select::make('term_id')
                    ->label('Term')
                    ->options(function () {
                        return Term::whereHas('schoolYear', fn ($q) => 
                            $q->where('is_current', true)
                        )
                        ->get()
                        ->mapWithKeys(function ($term) {
                            return [$term->id => $term->name . ' - ' . $term->schoolYear->name];
                        });
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedTerm = $state;
                        $this->loadStudents();
                    })
                    ->placeholder('Select a term'),
            ])
            ->statePath('data');
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
        // Calculate total when CA or Exam mark is updated
        if (str_contains($key, 'ca_mark') || str_contains($key, 'exam_mark')) {
            $parts = explode('.', $key);
            $index = (int) $parts[1];
            
            if (isset($this->marks[$index])) {
                $caMark = (float) ($this->marks[$index]['ca_mark'] ?? 0);
                $examMark = (float) ($this->marks[$index]['exam_mark'] ?? 0);
                
                if ($caMark > 0 || $examMark > 0) {
                    // Calculate total: 40% CA + 60% Exam
                    $totalMark = ($caMark * 0.4) + ($examMark * 0.6);
                    $this->marks[$index]['total_mark'] = round($totalMark, 2);
                    
                    // Get grade from grading scale
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
            \Filament\Notifications\Notification::make()
            ->title('Error')
            ->body('Please select class section, subject, and term.')
            ->danger()
            ->send();
            return;
        }

        DB::transaction(function () {
            foreach ($this->marks as $markData) {
                if (empty($markData['ca_mark']) && empty($markData['exam_mark'])) {
                    continue;
                }

                // Get or create term report
                $termReport = TermReport::firstOrCreate(
                    [
                        'enrollment_id' => $markData['enrollment_id'],
                        'term_id' => $this->selectedTerm,
                    ]
                );

                // Get or create subject report
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

                // Calculate total mark
                $subjectReport->calculateTotal();
                $subjectReport->save();
            }
        });

        \Filament\Notifications\Notification::make()
            ->title('Success')
            ->body('Marks saved successfully!')
            ->success()
            ->send();
        $this->loadStudents();
    }
}
