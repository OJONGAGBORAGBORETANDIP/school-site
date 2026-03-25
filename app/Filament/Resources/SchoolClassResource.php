<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'School Structure';
    }

    public static function getFormComponents(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g., Primary 1'),
            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('e.g., P1'),
            Forms\Components\TextInput::make('level')
                ->required()
                ->numeric()
                ->minValue(1)
                ->maxValue(9),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections'),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageSchoolClasses::route('/'),
        ];
    }
}
