<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAssignmentResource\Pages;
use App\Models\TeacherAssignment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use Filament\Schemas\Components\Utilities\Get;
use Closure;


class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = TeacherAssignment::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'School Structure';
    }

    public static function getFormComponents(): array
    {
        return [
            Forms\Components\Select::make('teacher_id')
                ->label('Teacher')
                ->relationship('teacher', 'name', fn ($query) => 
                    $query->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                )
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('class_section_id')
                ->label('Class Section')
                ->relationship('classSection', 'label')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('subject_id')
                ->label('Subject')
                ->relationship('subject', 'name')
                ->required()
                ->rules([
                    fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
            
                        $exists = TeacherAssignment::where('teacher_id', $get('teacher_id'))
                            ->where('class_section_id', $get('class_section_id'))
                            ->where('subject_id', $value)
                            ->exists();
            
                        if ($exists) {
                            $fail('This teacher is already assigned to this class and subject.');
                        }
                    },
                ])
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
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('classSection.label')
                    ->label('Class Section')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.code')
                    ->label('Subject Code')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->relationship('teacher', 'name', fn ($query) => 
                        $query->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                    )
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('class_section_id')
                    ->label('Class Section')
                    ->relationship('classSection', 'label')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
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
            'index' => Pages\ManageTeacherAssignments::route('/'),
        ];
    }
}
