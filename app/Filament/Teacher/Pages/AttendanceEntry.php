<?php

namespace App\Filament\Teacher\Pages;

use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Term;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AttendanceEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.teacher.pages.attendance-entry';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationLabel(): string
    {
        return 'Attendance Entry';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public ?array $data = [];

    public $selectedClassSection = null;
    public $selectedTerm = null;
    public $selectedDate = null;
    public $students = [];
    public $attendance = [];

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
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

                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->default(now())
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedDate = $state;
                        $this->loadStudents();
                    }),
            ])
            ->statePath('data');
    }

    public function loadStudents(): void
    {
        if (!$this->selectedClassSection || !$this->selectedTerm || !$this->selectedDate) {
            $this->students = [];
            $this->attendance = [];
            return;
        }

        $enrollments = Enrollment::where('class_section_id', $this->selectedClassSection)
            ->where('is_active', true)
            ->with('student')
            ->get();

        $this->students = $enrollments->map(function ($enrollment) {
            $existingAttendance = Attendance::where('enrollment_id', $enrollment->id)
                ->where('term_id', $this->selectedTerm)
                ->where('date', $this->selectedDate)
                ->first();

            return [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name,
                'admission_number' => $enrollment->student->admission_number,
                'status' => $existingAttendance?->status ?? 'present',
                'reason' => $existingAttendance?->reason ?? '',
                'attendance_id' => $existingAttendance?->id,
            ];
        })->toArray();

        // Initialize attendance array
        $this->attendance = collect($this->students)->mapWithKeys(function ($student) {
            return [$student['enrollment_id'] => [
                'status' => $student['status'],
                'reason' => $student['reason'],
            ]];
        })->toArray();
    }

    public function saveAttendance(): void
    {
        if (!$this->selectedClassSection || !$this->selectedTerm || !$this->selectedDate) {
        \Filament\Notifications\Notification::make()
            ->title('Error')
            ->body('Please select class section, term, and date.')
            ->danger()
            ->send();
            return;
        }

        DB::transaction(function () {
            foreach ($this->attendance as $enrollmentId => $attendanceData) {
                Attendance::updateOrCreate(
                    [
                        'enrollment_id' => $enrollmentId,
                        'term_id' => $this->selectedTerm,
                        'date' => $this->selectedDate,
                    ],
                    [
                        'status' => $attendanceData['status'],
                        'reason' => $attendanceData['reason'] ?? null,
                    ]
                );
            }
        });

        \Filament\Notifications\Notification::make()
            ->title('Success')
            ->body('Attendance saved successfully!')
            ->success()
            ->send();
        $this->loadStudents();
    }
}
