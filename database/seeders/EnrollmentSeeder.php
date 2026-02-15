<?php

namespace Database\Seeders;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $students = Student::all();
        $classes = SchoolClass::all();

        if ($students->isEmpty() || $classes->isEmpty()) {
            $this->command->warn('No students or classes found. Please run StudentSeeder and SchoolClassSeeder first.');
            return;
        }

        // Enroll students in different classes
        $enrollments = [
            // Primary 1
            ['student_index' => 0, 'class_level' => 1, 'section' => 'A'],
            ['student_index' => 1, 'class_level' => 1, 'section' => 'A'],
            // Primary 2
            ['student_index' => 2, 'class_level' => 2, 'section' => 'A'],
            ['student_index' => 3, 'class_level' => 2, 'section' => 'A'],
            // Primary 3
            ['student_index' => 4, 'class_level' => 3, 'section' => 'A'],
            ['student_index' => 5, 'class_level' => 3, 'section' => 'A'],
        ];

        foreach ($enrollments as $enrollmentData) {
            $student = $students[$enrollmentData['student_index']] ?? null;
            if (!$student) continue;

            $class = $classes->where('level', $enrollmentData['class_level'])->first();
            if (!$class) continue;

            $section = ClassSection::where('school_class_id', $class->id)
                ->where('name', $enrollmentData['section'])
                ->first();

            if ($section) {
                Enrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'class_section_id' => $section->id,
                        'school_year_id' => $currentYear->id,
                    ],
                    [
                        'class_level' => $class->level,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
