<?php

namespace App\Filament\Headteacher\Resources;

use App\Filament\Headteacher\Resources\TermReportResource\Pages;
use App\Models\TermReport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TermReportResource extends Resource
{
    protected static ?string $model = TermReport::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Report Cards';
    }

    public static function getNavigationLabel(): string
    {
        return 'Term Reports';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('enrollment_id')
                            ->relationship('enrollment', 'id', fn (Builder $query) => $query->with(['student', 'classSection']))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->student->first_name . ' ' . $record->student->last_name . ' - ' . $record->classSection->label)
                            ->disabled(),
                        Forms\Components\Select::make('term_id')
                            ->relationship('term', 'name')
                            ->disabled(),
                    ])->columns(2),
                Forms\Components\Section::make('Performance')
                    ->schema([
                        Forms\Components\TextInput::make('average')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('position')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('class_average')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('class_size')
                            ->numeric()
                            ->disabled(),
                    ])->columns(4),
                Forms\Components\Section::make('Remarks')
                    ->schema([
                        Forms\Components\Textarea::make('class_teacher_remark')
                            ->rows(3)
                            ->disabled(),
                        Forms\Components\Textarea::make('headteacher_remark')
                            ->rows(3)
                            ->label('Your Remark')
                            ->placeholder('Add your remark here...'),
                        Forms\Components\Toggle::make('is_approved_by_headteacher')
                            ->label('Approve Report Card')
                            ->helperText('Once approved, parents can view this report card'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.student.first_name')
                    ->label('Student')
                    ->formatStateUsing(fn ($record) => $record->enrollment->student->first_name . ' ' . $record->enrollment->student->last_name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.classSection.label')
                    ->label('Class')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term.name')
                    ->label('Term')
                    ->sortable(),
                Tables\Columns\TextColumn::make('average')
                    ->label('Average')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved_by_headteacher')
                    ->label('Approved')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('term_id')
                    ->relationship('term', 'name')
                    ->label('Term'),
                Tables\Filters\SelectFilter::make('enrollment.class_section_id')
                    ->relationship('enrollment.classSection', 'label')
                    ->label('Class'),
                Tables\Filters\TernaryFilter::make('is_approved_by_headteacher')
                    ->label('Approval Status')
                    ->placeholder('All reports')
                    ->trueLabel('Approved')
                    ->falseLabel('Pending'),
            ])
            ->actions([
                \Filament\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (TermReport $record): string => static::getUrl('edit', ['record' => $record])),
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (TermReport $record) {
                        $record->update([
                            'is_approved_by_headteacher' => true,
                        ]);
                    })
                    ->visible(fn (TermReport $record) => !$record->is_approved_by_headteacher),
                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (TermReport $record) {
                        $record->update([
                            'is_approved_by_headteacher' => false,
                        ]);
                    })
                    ->visible(fn (TermReport $record) => $record->is_approved_by_headteacher),
            ])
            ->bulkActions([
                \Filament\Actions\Action::make('bulk_approve')
                    ->label('Approve Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $records->each(function ($record) {
                            $record->update(['is_approved_by_headteacher' => true]);
                        });
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTermReports::route('/'),
            'edit' => Pages\EditTermReport::route('/{record}/edit'),
        ];
    }
}
