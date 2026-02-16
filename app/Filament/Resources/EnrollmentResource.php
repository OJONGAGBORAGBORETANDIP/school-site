<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-plus';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Student Management';
    }

    public static function getFormComponents(): array
    {
        return [
            Forms\Components\Select::make('student_id')
                ->label('Student')
                ->relationship('student', 'admission_number')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->admission_number})")
                ->required()
                ->searchable(['first_name', 'last_name', 'admission_number'])
                ->preload(),
            Forms\Components\Select::make('class_section_id')
                ->label('Class Section')
                ->relationship('classSection', 'label')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('school_year_id')
                ->label('School Year')
                ->relationship('schoolYear', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('class_level')
                ->label('Class Level')
                ->numeric()
                ->minValue(1)
                ->maxValue(6)
                ->helperText('Primary level (1-6)'),
            Forms\Components\Toggle::make('is_active')
                ->label('Active Enrollment')
                ->default(true),
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
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Student')
                    ->formatStateUsing(fn ($record) => "{$record->student->first_name} {$record->student->last_name}")
                    ->searchable(['student.first_name', 'student.last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.admission_number')
                    ->label('Admission No.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('classSection.label')
                    ->label('Class Section')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schoolYear.name')
                    ->label('School Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('class_level')
                    ->label('Level')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_year_id')
                    ->label('School Year')
                    ->relationship('schoolYear', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('class_section_id')
                    ->label('Class Section')
                    ->relationship('classSection', 'label')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            'index' => Pages\ManageEnrollments::route('/'),
        ];
    }
}
