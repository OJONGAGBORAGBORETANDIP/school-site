<?php

namespace App\Filament\Headteacher\Resources\TermReportResource\Pages;

use App\Filament\Headteacher\Resources\TermReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTermReports extends ListRecords
{
    protected static string $resource = TermReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
