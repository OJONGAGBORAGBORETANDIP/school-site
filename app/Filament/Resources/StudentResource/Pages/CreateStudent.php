<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
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
