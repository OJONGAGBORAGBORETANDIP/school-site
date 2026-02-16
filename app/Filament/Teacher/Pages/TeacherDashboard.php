<?php

namespace App\Filament\Teacher\Pages;

use Filament\Pages\Page;

class TeacherDashboard extends Page
{
    protected string $view = 'filament.teacher.pages.teacher-dashboard';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-home';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
