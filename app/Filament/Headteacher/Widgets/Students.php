<?php

namespace App\Filament\Headteacher\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Student;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class Students extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Student::query())
            ->columns([
                TextColumn::make('first_name')->searchable()->sortable(),
                TextColumn::make('last_name')->searchable()->sortable(),
                TextColumn::make('other_names')->searchable()->sortable(),
                TextColumn::make('gender')->badge(),
                TextColumn::make('date_of_birth')->date(),
                TextColumn::make('enrollments.classSection.label')->badge()->separator(','),
            ])->defaultSort('first_name')
            ->filters([
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
