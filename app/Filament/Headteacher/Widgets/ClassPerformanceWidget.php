<?php

namespace App\Filament\Headteacher\Widgets;

use App\Models\TermReport;
use App\Models\ClassSection;
use Filament\Widgets\ChartWidget;

class ClassPerformanceWidget extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Class Average Performance';
    }

    public static function getSort(): int
    {
        return 2;
    }

    protected function getData(): array
    {
        $currentTerm = \App\Models\Term::whereHas('schoolYear', function ($query) {
            $query->where('is_current', true);
        })->latest('number')->first();

        if (!$currentTerm) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $sections = ClassSection::with(['enrollments.termReports' => function ($query) use ($currentTerm) {
            $query->where('term_id', $currentTerm->id);
        }])->get();

        $labels = [];
        $averages = [];

        foreach ($sections as $section) {
            $reports = $section->enrollments->flatMap->termReports;
            if ($reports->count() > 0) {
                $avg = $reports->avg('average');
                $labels[] = $section->label;
                $averages[] = round($avg, 2);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Class Average',
                    'data' => $averages,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
