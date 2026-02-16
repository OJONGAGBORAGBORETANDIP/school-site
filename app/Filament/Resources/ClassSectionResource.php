<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassSectionResource\Pages;
use App\Models\ClassSection;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;

class ClassSectionResource extends Resource
{
    protected static ?string $model = ClassSection::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'School Structure';
    }

    public static function getFormComponents(): array
    {
        return [
            Forms\Components\Select::make('school_class_id')
                ->label('School Class')
                ->relationship('schoolClass', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g., A, B, C'),
            Forms\Components\TextInput::make('label')
                ->maxLength(255)
                ->placeholder('e.g., P1A, P1B')
                ->helperText('Auto-generated if left empty'),
            Forms\Components\TextInput::make('capacity')
                ->numeric()
                ->minValue(1)
                ->helperText('Maximum number of students'),
            Forms\Components\Select::make('class_teacher_id')
                ->label('Class Teacher')
                ->relationship('classTeacher', 'name', fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'teacher')))
                ->searchable()
                ->preload(),
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
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('classTeacher.name')
                    ->label('Class Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Students')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_class_id')
                    ->label('School Class')
                    ->relationship('schoolClass', 'name')
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
            'index' => Pages\ManageClassSections::route('/'),
        ];
    }
}
