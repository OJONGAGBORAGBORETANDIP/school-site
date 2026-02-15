<?php

namespace App\Filament\Headteacher\Pages;

use Filament\Pages\Page;
use App\Models\TermReport;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\SubjectReport;
use Illuminate\Support\Facades\DB;

class PerformanceAnalytics extends Page
{
    protected string $view = 'filament.headteacher.pages.performance-analytics';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationLabel(): string
    {
        return 'Performance Analytics';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public function getHeading(): string
    {
        return 'Performance Analytics';
    }

    public function getSubheading(): ?string
    {
        return 'Overview of academic performance across classes and subjects';
    }

    public function getClassPerformanceData(): array
    {
        $currentTerm = \App\Models\Term::whereHas('schoolYear', function ($query) {
            $query->where('is_current', true);
        })->latest('number')->first();

        if (!$currentTerm) {
            return [];
        }

        $sections = ClassSection::with(['enrollments.termReports' => function ($query) use ($currentTerm) {
            $query->where('term_id', $currentTerm->id);
        }])->get();

        $data = [];
        foreach ($sections as $section) {
            $reports = $section->enrollments->flatMap->termReports;
            if ($reports->count() > 0) {
                $data[] = [
                    'class' => $section->label,
                    'average' => round($reports->avg('average'), 2),
                    'students' => $reports->count(),
                    'top_score' => round($reports->max('average'), 2),
                    'lowest_score' => round($reports->min('average'), 2),
                ];
            }
        }

        return $data;
    }

    public function getSubjectPerformanceData(): array
    {
        $currentTerm = \App\Models\Term::whereHas('schoolYear', function ($query) {
            $query->where('is_current', true);
        })->latest('number')->first();

        if (!$currentTerm) {
            return [];
        }

        $subjectReports = SubjectReport::whereHas('termReport', function ($query) use ($currentTerm) {
            $query->where('term_id', $currentTerm->id);
        })
            ->select('subject_id', DB::raw('AVG(total_mark) as avg_mark'), DB::raw('COUNT(*) as count'))
            ->groupBy('subject_id')
            ->with('subject')
            ->get();

        return $subjectReports->map(function ($report) {
            return [
                'subject' => $report->subject->name,
                'average' => round($report->avg_mark, 2),
                'count' => $report->count,
            ];
        })->toArray();
    }
}
