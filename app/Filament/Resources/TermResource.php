<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TermResource\Pages;
use App\Models\Term;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;

class TermResource extends Resource
{
    protected static ?string $model = Term::class;

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'School Structure';
    }

    public static function getFormComponents(): array
    {
        return [
            Forms\Components\Select::make('school_year_id')
                ->label('School Year')
                ->relationship('schoolYear', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('number')
                ->required()
                ->numeric()
                ->minValue(1)
                ->maxValue(3)
                ->helperText('Term number: 1, 2, or 3'),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g., 1st Term, 2nd Term, 3rd Term'),
            Forms\Components\DatePicker::make('starts_at')
                ->label('Start Date'),
            Forms\Components\DatePicker::make('ends_at')
                ->label('End Date'),
            Forms\Components\DatePicker::make('results_published_at')
                ->label('Results Published Date'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(static::getFormComponents());
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
                Tables\Columns\TextColumn::make('results_published_at')
                    ->date()
                    ->label('Published')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_year_id')
                    ->label('School Year')
                    ->relationship('schoolYear', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->form(static::getFormComponents())
                    ->fillForm(fn ($record) => $record->toArray())
                    ->action(fn ($record, array $data) => $record->update($data)),
                \Filament\Actions\Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->delete()),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Delete selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->delete()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTerms::route('/'),
        ];
    }
}
