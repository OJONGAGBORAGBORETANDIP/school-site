<?php

namespace App\Filament\Headteacher\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class HeadteacherDashboard extends BaseDashboard
{
    protected string $view = 'filament.headteacher.pages.headteacher-dashboard';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-home';
    }
}
