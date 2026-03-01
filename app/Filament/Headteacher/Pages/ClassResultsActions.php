<?php

namespace App\Filament\Headteacher\Pages;

use App\Models\ClassSection;
use App\Models\Term;
use App\Services\GenerateTermReportService;
use App\Services\HeadteacherApprovalService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;
/**
 * Headteacher bulk actions: Generate Reports for Class, Approve Class Results.
 */
class ClassResultsActions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calculator';
    protected static string | UnitEnum | null $navigationGroup = 'Report Cards';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Class Results';
    protected static ?string $title = 'Class Results';
    protected string $view = 'filament.headteacher.pages.class-results-actions';

    public ?array $data = [];

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
                    ->searchable(),
            ])
            ->statePath('data');
    }
    public function generateReports(): void
    {
        $data = $this->form->getState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);

        if (!$classSectionId || !$termId) {
            Notification::make()->title('Select class section and term.')->danger()->send();
            return;
        }

        try {
            $service = app(GenerateTermReportService::class);
            $result = $service->generate($classSectionId, $termId);
            Notification::make()
                ->title('Reports generated.')
                ->body("Created: {$result['generated']}, Updated: {$result['updated']}.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function approveClassResults(): void
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
            if (!$service->canApproveClassResults($classSectionId, $termId)) {
                Notification::make()
                    ->title('Cannot approve')
                    ->body('Not all subject reports for this class and term are submitted. Scores must be marked as "Pending Headmaster Approval" by the teacher first.')
                    ->danger()
                    ->send();
                return;
            }
            $count = $service->approveClassResults($classSectionId, $termId);
            Notification::make()->title('Class results approved.')->body("{$count} term report(s) approved. Parents can now view and download report cards for this class and term.")->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function declineClassResults(): void
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
            $count = $service->declineClassResults($classSectionId, $termId);
            Notification::make()->title('Class results declined.')->body("{$count} term report(s) returned. The teacher can now correct the scores and resubmit.")->warning()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function getClassStatus(): ?string
    {
        $data = $this->form->getRawState();
        $classSectionId = (int) ($data['class_section_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        if (!$classSectionId || !$termId) {
            return null;
        }
        return app(HeadteacherApprovalService::class)->getClassStatus($classSectionId, $termId);
    }
}
