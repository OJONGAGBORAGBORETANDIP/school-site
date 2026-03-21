<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureUserIsHeadteacher;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Contracts\View\View;

class HeadteacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('headteacher')
            ->path('headteacher')
            ->login()
            ->brandName('El-Nissi Report Card System - Headteacher')
            ->renderHook(
                'panels::body.start',
                fn (): View => view('components.view-background')
            )
            ->colors([
                // 'primary' => Color::Green,
                'primary' => '#00FF7F',
            ])
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Headteacher/Resources'), for: 'App\Filament\Headteacher\Resources')
            ->discoverPages(in: app_path('Filament/Headteacher/Pages'), for: 'App\Filament\Headteacher\Pages')
            ->pages([
                \App\Filament\Headteacher\Pages\HeadteacherDashboard::class,
                \App\Filament\Headteacher\Pages\ClassResultsActions::class,
                \App\Filament\Headteacher\Pages\PendingApprovals::class,
                \App\Filament\Headteacher\Pages\PerformanceAnalytics::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Headteacher/Widgets'), for: 'App\Filament\Headteacher\Widgets')
            ->widgets([
                \App\Filament\Headteacher\Widgets\ReportCardStatsWidget::class,
                \App\Filament\Headteacher\Widgets\ClassPerformanceWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsHeadteacher::class,
            ]);
    }
}
