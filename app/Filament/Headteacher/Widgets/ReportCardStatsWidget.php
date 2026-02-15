<?php

namespace App\Filament\Headteacher\Widgets;

use App\Models\TermReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportCardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentTerm = \App\Models\Term::whereHas('schoolYear', function ($query) {
            $query->where('is_current', true);
        })->latest('number')->first();

        if (!$currentTerm) {
            return [
                Stat::make('Total Reports', '0')
                    ->description('No current term found')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        $totalReports = TermReport::where('term_id', $currentTerm->id)->count();
        $approvedReports = TermReport::where('term_id', $currentTerm->id)
            ->where('is_approved_by_headteacher', true)
            ->count();
        $pendingReports = $totalReports - $approvedReports;
        $approvalRate = $totalReports > 0 ? round(($approvedReports / $totalReports) * 100, 1) : 0;

        return [
            Stat::make('Total Reports', $totalReports)
                ->description('For ' . $currentTerm->name)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Approved Reports', $approvedReports)
                ->description($approvalRate . '% approval rate')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pending Approval', $pendingReports)
                ->description('Awaiting your review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
