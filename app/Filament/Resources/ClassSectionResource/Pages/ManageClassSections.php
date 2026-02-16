<?php

namespace App\Filament\Resources\ClassSectionResource\Pages;

use App\Filament\Resources\ClassSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClassSections extends ManageRecords
{
    protected static string $resource = ClassSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
