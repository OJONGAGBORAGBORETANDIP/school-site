<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core structure
            RolesSeeder::class,
            SchoolYearSeeder::class,
            TermSeeder::class,
            SchoolClassSeeder::class,
            ClassSectionSeeder::class,
            SubjectSeeder::class,
            GradingScaleSeeder::class,
            
            // Users and relationships
            UserSeeder::class,
            StudentSeeder::class,
            EnrollmentSeeder::class,
            TeacherAssignmentSeeder::class,
            ParentStudentSeeder::class,
        ]);
    }
}
