<?php

namespace App\Filament\Teacher\Pages;

use App\Models\ClassSection;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MyClasses extends Page
{
    protected string $view = 'filament.teacher.pages.my-classes';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationLabel(): string
    {
        return 'My Classes';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public function getClasses()
    {
        $teacherId = auth()->id();
        
        return ClassSection::whereHas('teacherAssignments', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->with(['schoolClass', 'classTeacher', 'enrollments' => function ($query) {
            $query->where('is_active', true);
        }])
        ->get()
        ->map(function ($section) use ($teacherId) {
            $subjects = DB::table('teacher_assignments')
                ->where('teacher_id', $teacherId)
                ->where('class_section_id', $section->id)
                ->join('subjects', 'teacher_assignments.subject_id', '=', 'subjects.id')
                ->select('subjects.name', 'subjects.code')
                ->get();
            
            return [
                'id' => $section->id,
                'label' => $section->label,
                'class_name' => $section->schoolClass->name,
                'class_teacher' => $section->classTeacher?->name,
                'student_count' => $section->enrollments->count(),
                'subjects' => $subjects,
            ];
        });
    }
}
