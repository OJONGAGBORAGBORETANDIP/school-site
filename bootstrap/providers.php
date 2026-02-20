<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\HeadteacherPanelProvider::class,
    // TeacherPanelProvider removed - using Livewire instead of Filament for teachers
    // App\Providers\Filament\TeacherPanelProvider::class,
];
