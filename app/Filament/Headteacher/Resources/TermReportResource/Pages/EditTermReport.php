<?php

namespace App\Filament\Headteacher\Resources\TermReportResource\Pages;

use App\Filament\Headteacher\Resources\TermReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTermReport extends EditRecord
{
    protected static string $resource = TermReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('approve')
                ->label('Approve Report Card')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'is_approved_by_headteacher' => true,
                    ]);
                    $this->notification()
                        ->success()
                        ->title('Report Card Approved')
                        ->body('The report card has been approved and is now visible to parents.')
                        ->send();
                })
                ->visible(fn () => !$this->record->is_approved_by_headteacher),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
