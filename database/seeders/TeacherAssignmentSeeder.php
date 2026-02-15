<?php

namespace Database\Seeders;

use App\Models\ClassSection;
use App\Models\Role;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        $sections = ClassSection::all();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $sections->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('No teachers, sections, or subjects found. Please run UserSeeder, ClassSectionSeeder, and SubjectSeeder first.');
            return;
        }

        // Assign subject teachers (assign a few subjects to teachers)
        $subjectAssignments = [
            ['teacher_index' => 0, 'subject_code' => 'ENG'],
            ['teacher_index' => 0, 'subject_code' => 'MATH'],
            ['teacher_index' => 1, 'subject_code' => 'FRE'],
            ['teacher_index' => 1, 'subject_code' => 'SCI'],
            ['teacher_index' => 2, 'subject_code' => 'SOC'],
            ['teacher_index' => 2, 'subject_code' => 'REL'],
        ];

        foreach ($subjectAssignments as $assignment) {
            if (!isset($teachers[$assignment['teacher_index']])) continue;

            $subject = $subjects->where('code', $assignment['subject_code'])->first();
            if (!$subject) continue;

            // Assign to first section for simplicity
            $section = $sections->first();
            if ($section) {
                TeacherAssignment::firstOrCreate(
                    [
                        'teacher_id' => $teachers[$assignment['teacher_index']]->id,
                        'class_section_id' => $section->id,
                        'subject_id' => $subject->id,
                    ]
                );
            }
        }
    }
}
