<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'English Language', 'code' => 'ENG', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => true],
            ['name' => 'French Language', 'code' => 'FRE', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => true],
            ['name' => 'Mathematics', 'code' => 'MATH', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => true],
            ['name' => 'Science', 'code' => 'SCI', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => true],
            ['name' => 'Social Studies', 'code' => 'SOC', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => true],
            ['name' => 'Religious Studies', 'code' => 'REL', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => true],
            ['name' => 'Physical Education', 'code' => 'PE', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => false],
            ['name' => 'Arts and Crafts', 'code' => 'ART', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => false],
            ['name' => 'Computer Studies', 'code' => 'COMP', 'min_level' => 1, 'max_level' => 6, 'is_compulsory' => false],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                $subject
            );
        }
    }
}
