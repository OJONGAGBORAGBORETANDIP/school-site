<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create current academic year (2024-2025)
        SchoolYear::firstOrCreate(
            [
                'name' => '2024-2025',
            ],
            [
                'starts_at' => '2024-09-01',
                'ends_at' => '2025-06-30',
                'is_current' => true,
            ]
        );

        // Create previous year (2023-2024)
        SchoolYear::firstOrCreate(
            [
                'name' => '2023-2024',
            ],
            [
                'starts_at' => '2023-09-01',
                'ends_at' => '2024-06-30',
                'is_current' => false,
            ]
        );
    }
}
