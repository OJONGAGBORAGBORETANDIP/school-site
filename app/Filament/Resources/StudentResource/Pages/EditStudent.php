<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('parents');
        foreach ($this->record->parents as $parent) {
            $rel = $parent->pivot->relationship ?? null;
            if ($rel === 'father') {
                $data['father_id'] = $parent->id;
            } elseif ($rel === 'mother') {
                $data['mother_id'] = $parent->id;
            } elseif ($rel === 'guardian') {
                $data['guardian_id'] = $parent->id;
            }
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncGuardians($this->record, $this->form->getState());
    }

    protected function syncGuardians($student, array $data): void
    {
        $sync = [];
        if (! empty($data['father_id'])) {
            $sync[(int) $data['father_id']] = ['relationship' => 'father'];
        }
        if (! empty($data['mother_id'])) {
            $sync[(int) $data['mother_id']] = ['relationship' => 'mother'];
        }
        if (! empty($data['guardian_id'])) {
            $sync[(int) $data['guardian_id']] = ['relationship' => 'guardian'];
        }
        $student->parents()->sync($sync);
    }
}
