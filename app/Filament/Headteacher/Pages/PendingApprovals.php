<?php

namespace App\Filament\Headteacher\Pages;

use App\Models\ClassSection;
use App\Models\Term;
use App\Services\HeadteacherApprovalService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use BackedEnum;
use UnitEnum;

class PendingApprovals extends Page implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.headteacher.pages.pending-approvals';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string|UnitEnum|null $navigationGroup = 'Report Cards';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Pending Approvals';
    protected static ?string $title = 'Pending CA & Exam Approvals';

    public ?array $data = [];

    /** Rejection reason when rejecting (user fills before clicking Reject). */
    public string $rejectReasonCa = '';
    public string $rejectReasonExam = '';

    /** View marks overlay: which subject and type (ca|exam). */
    public ?int $viewMarksSubjectId = null;
    public ?string $viewMarksType = null;
    public string $viewMarksSubjectName = '';

    /** Sort state for Flux table (subject column). */
    public string $sortBy = 'subject';
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $this->form->fill([
            'class_section_id' => null,
            'term_id' => null,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->schema([
                Select::make('class_section_id')
                    
                    ->label('Class Section')
                    ->options(
                        ClassSection::query()
                            ->with('schoolClass')
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => $s->label . ' (' . ($s->schoolClass->name ?? '') . ')'])
                    )
                    ->required()
                    ->searchable()
                    ->live(),
                Select::make('term_id')
                    ->label('Term')
                    ->options(
                        Term::query()
                            ->with('schoolYear')
                            ->orderByDesc('school_year_id')
                            ->orderBy('number')
                            ->get()
                            ->mapWithKeys(fn ($t) => [$t->id => $t->name . ' – ' . $t->schoolYear->name])
                    )
                    ->required()
                    ->searchable()
                    ->live(),
            ])
            ->statePath('data');
    }

    /**
     * Combined pending CA + Exam records for the table (unique keys for Livewire).
     *
     * @return Collection<int, array{type: string, id: int, name: string}>
     */
    public function getTableRecords(): Collection
    {
        $ca = $this->getSortedPendingCa();
        $exam = $this->getSortedPendingExam();
        $records = [];
        foreach ($ca as $subject) {
            $key = 'ca-' . $subject['id'];
            $records[$key] = [
                '__key' => $key,
                'type' => 'ca',
                'id' => $subject['id'],
                'name' => $subject['name'],
            ];
        }
        foreach ($exam as $subject) {
            $key = 'exam-' . $subject['id'];
            $records[$key] = [
                '__key' => $key,
                'type' => 'exam',
                'id' => $subject['id'],
                'name' => $subject['name'],
            ];
        }
        return collect($records);
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->getTableRecords())
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (array $record): string => $record['type'] === 'ca' ? 'CA' : 'Exam')
                    ->badge()
                    ->color(fn (array $record): string => $record['type'] === 'ca' ? 'warning' : 'info'),
                TextColumn::make('name')
                    ->label('Subject')
                    ->searchable(false)
                    ->sortable(false),
            ])
            ->actions([
                \Filament\Actions\Action::make('view_marks')
                    ->label('View marks')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->action(function (array $record): void {
                        if ($record['type'] === 'ca') {
                            $this->openViewMarksCa($record['id']);
                        } else {
                            $this->openViewMarksExam($record['id']);
                        }
                    }),
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (array $record): void {
                        if ($record['type'] === 'ca') {
                            $this->doApproveCa($record['id']);
                        } else {
                            $this->doApproveExam($record['id']);
                        }
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject submission')
                    ->modalDescription(fn (array $record): string => 'Enter rejection reason below. Teacher will be notified and can resubmit.')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Rejection reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $record, array $data): void {
                        $reason = $data['reason'] ?? '';
                        if ($record['type'] === 'ca') {
                            $this->rejectCaWithReason($record['id'], $reason);
                        } else {
                            $this->rejectExamWithReason($record['id'], $reason);
                        }
                    }),
            ])
            ->striped()
            ->emptyStateHeading('No pending submissions')
            ->emptyStateDescription('Select a class section and term above. Pending CA or Exam submissions will appear here.');
    }

    private function rejectCaWithReason(int $subjectId, string $reason): void
    {
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }
        try {
            app(HeadteacherApprovalService::class)->rejectCa($classSectionId, $termId, $subjectId, $reason);
            Notification::make()->title('CA rejected')->body('Teacher has been notified and can resubmit.')->warning()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    private function rejectExamWithReason(int $subjectId, string $reason): void
    {
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }
        try {
            app(HeadteacherApprovalService::class)->rejectExam($classSectionId, $termId, $subjectId, $reason);
            Notification::make()->title('Exam rejected')->body('Teacher has been notified and can resubmit.')->warning()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function getPendingCa(): array
    {
        $data = $this->form->getRawState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            return [];
        }
        return app(HeadteacherApprovalService::class)->getPendingCaSubmissions($classSectionId, $termId);
    }

    public function getPendingExam(): array
    {
        $data = $this->form->getRawState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            return [];
        }
        return app(HeadteacherApprovalService::class)->getPendingExamSubmissions($classSectionId, $termId);
    }

    /** Sorted pending CA for Flux table (by subject name). */
    public function getSortedPendingCa(): array
    {
        $items = $this->getPendingCa();
        usort($items, function ($a, $b) {
            $cmp = strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            return $this->sortDirection === 'asc' ? $cmp : -$cmp;
        });
        return $items;
    }

    /** Sorted pending Exam for Flux table (by subject name). */
    public function getSortedPendingExam(): array
    {
        $items = $this->getPendingExam();
        usort($items, function ($a, $b) {
            $cmp = strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            return $this->sortDirection === 'asc' ? $cmp : -$cmp;
        });
        return $items;
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Marks rows for the "View marks" modal (student name + CA or exam mark).
     *
     * @return array<int, array{student_name: string, ca_mark?: float|int|null, exam_mark?: float|int|null}>
     */
    public function getViewMarksRows(): array
    {
        if ($this->viewMarksType === null || $this->viewMarksSubjectId === null) {
            return [];
        }
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            return [];
        }
        $service = app(HeadteacherApprovalService::class);
        if ($this->viewMarksType === 'ca') {
            return $service->getPendingCaMarks($classSectionId, $termId, $this->viewMarksSubjectId);
        }
        return $service->getPendingExamMarks($classSectionId, $termId, $this->viewMarksSubjectId);
    }

    public function openViewMarksCa(int $subjectId): void
    {
        $pending = $this->getPendingCa();
        $name = collect($pending)->firstWhere('id', $subjectId)['name'] ?? 'Subject';
        $this->viewMarksType = 'ca';
        $this->viewMarksSubjectId = $subjectId;
        $this->viewMarksSubjectName = $name;
    }

    public function openViewMarksExam(int $subjectId): void
    {
        $pending = $this->getPendingExam();
        $name = collect($pending)->firstWhere('id', $subjectId)['name'] ?? 'Subject';
        $this->viewMarksType = 'exam';
        $this->viewMarksSubjectId = $subjectId;
        $this->viewMarksSubjectName = $name;
    }

    public function closeViewMarks(): void
    {
        $this->viewMarksType = null;
        $this->viewMarksSubjectId = null;
        $this->viewMarksSubjectName = null;
    }

    public function approveCa(int $subjectId): void
    {
        $this->doApproveCa($subjectId);
    }

    public function approveExam(int $subjectId): void
    {
        $this->doApproveExam($subjectId);
    }

    public function rejectCa(int $subjectId): void
    {
        $reason = trim($this->rejectReasonCa);
        if ($reason === '') {
            Notification::make()->title('Enter a rejection reason for CA first.')->danger()->send();
            return;
        }
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }
        try {
            app(HeadteacherApprovalService::class)->rejectCa($classSectionId, $termId, $subjectId, $reason);
            Notification::make()->title('CA rejected')->body('Teacher has been notified and can resubmit.')->warning()->send();
            $this->rejectReasonCa = '';
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function rejectExam(int $subjectId): void
    {
        $reason = trim($this->rejectReasonExam);
        if ($reason === '') {
            Notification::make()->title('Enter a rejection reason for Exam first.')->danger()->send();
            return;
        }
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }
        try {
            app(HeadteacherApprovalService::class)->rejectExam($classSectionId, $termId, $subjectId, $reason);
            Notification::make()->title('Exam rejected')->body('Teacher has been notified and can resubmit.')->warning()->send();
            $this->rejectReasonExam = '';
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    private function doApproveCa(int $subjectId): void
    {
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }
        try {
            $service = app(HeadteacherApprovalService::class);
            $count = $service->approveCa($classSectionId, $termId, $subjectId);
            Notification::make()->title('CA approved')->body("{$count} record(s) approved.")->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    private function doApproveExam(int $subjectId): void
    {
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }
        try {
            $service = app(HeadteacherApprovalService::class);
            $count = $service->approveExam($classSectionId, $termId, $subjectId);
            Notification::make()->title('Exam approved')->body("{$count} record(s) approved.")->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

}
