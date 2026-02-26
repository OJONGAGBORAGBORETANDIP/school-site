<?php

namespace App\Filament\Headteacher\Resources;

use App\Filament\Headteacher\Resources\TermResource\Pages;
use App\Models\Term;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TermResource extends Resource
{
    protected static ?string $model = Term::class;

    protected static ?int $navigationSort = 0;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return 'Terms';
    }

    public static function getModelLabel(): string
    {
        return 'Term';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolYear.name')
                    ->label('School Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : '')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_year_id')
                    ->label('School Year')
                    ->relationship('schoolYear', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\Action::make('setActive')
                    ->label('Set as active term')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Set as active term')
                    ->modalDescription(fn (Term $record) => "Teachers will enter marks for \"{$record->name} - {$record->schoolYear->name}\". Other terms will be read-only.")
                    ->action(function (Term $record) {
                        Term::query()->update(['is_active' => false]);
                        $record->update(['is_active' => true]);
                    })
                    ->visible(fn (Term $record) => !$record->is_active),
                \Filament\Actions\Action::make('publishResults')
                    ->label('Publish Results')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Publish results')
                    ->modalDescription(fn (Term $record) => "Parents will be able to view report cards for \"{$record->name} - {$record->schoolYear->name}\". Teachers will no longer be able to edit marks.")
                    ->action(function (Term $record) {
                        app(\App\Services\PublishResultsService::class)->publishTermResults($record->id);
                    })
                    ->visible(fn (Term $record) => $record->results_published_at === null),
            ])
            ->defaultSort('school_year_id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTerms::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
