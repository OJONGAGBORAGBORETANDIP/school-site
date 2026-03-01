<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (!$currentYear) {
            $this->command->warn('No current school year found. Please run SchoolYearSeeder first.');
            return;
        }

        $terms = [
            [
                'school_year_id' => $currentYear->id,
                'number' => 1,
                'name' => '1st Term',
                'starts_at' => '2024-09-01',
                'ends_at' => '2024-12-20',
                'results_published_at' => null,
            ],
            [
                'school_year_id' => $currentYear->id,
                'number' => 2,
                'name' => '2nd Term',
                'starts_at' => '2025-01-06',
                'ends_at' => '2025-04-10',
                'results_published_at' => null,
            ],
            [
                'school_year_id' => $currentYear->id,
                'number' => 3,
                'name' => '3rd Term',
                'starts_at' => '2025-04-21',
                'ends_at' => '2025-06-30',
                'results_published_at' => null,
            ],
        ];

        foreach ($terms as $term) {
            Term::firstOrCreate(
                [
                    'school_year_id' => $term['school_year_id'],
                    'number' => $term['number'],
                ],
                $term
            );
        }

        // Set the first term of the current year as active so teachers can enter marks
        Term::where('school_year_id', $currentYear->id)->update(['is_active' => false]);
        Term::where('school_year_id', $currentYear->id)->where('number', 1)->update(['is_active' => true]);
    }
}
