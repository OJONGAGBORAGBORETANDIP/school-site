<?php

namespace App\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Component;

class ViewMarksTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    /** @var array<int, array{student_name: string, ca_mark?: float|int|null, exam_mark?: float|int|null}> */
    public array $rows = [];

    /** @var 'ca'|'exam' */
    public string $markType = 'ca';

    public function getTableRecords(): Collection
    {
        $records = [];
        foreach ($this->rows as $index => $row) {
            $key = 'row-' . $index;
            $records[$key] = [
                '__key' => $key,
                'student_name' => $row['student_name'] ?? '—',
                'ca_mark' => $row['ca_mark'] ?? null,
                'exam_mark' => $row['exam_mark'] ?? null,
            ];
        }
        return collect($records);
    }

    public function table(Table $table): Table
    {
        $markType = $this->markType;
        return $table
            ->records(fn (): Collection => $this->getTableRecords())
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student'),
                TextColumn::make($markType === 'ca' ? 'ca_mark' : 'exam_mark')
                    ->label($markType === 'ca' ? 'CA mark (out of 30)' : 'Exam mark (out of 70)')
                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? $state : '—')
                    ->alignEnd(),
            ])
            ->striped()
            ->emptyStateHeading('No marks to display');
    }

    public function render()
    {
        return view('livewire.view-marks-table');
    }
}
