<?php

namespace Database\Seeders;

use App\Models\GradingScale;
use Illuminate\Database\Seeder;

class GradingScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scales = [
            ['min_mark' => 18, 'max_mark' => 20, 'grade' => 'A', 'remark' => 'Excellent'],
            ['min_mark' => 16, 'max_mark' => 17, 'grade' => 'B', 'remark' => 'Very Good'],
            ['min_mark' => 14, 'max_mark' => 15, 'grade' => 'C', 'remark' => 'Good'],
            ['min_mark' => 12, 'max_mark' => 13, 'grade' => 'D', 'remark' => 'Fair'],
            ['min_mark' => 10, 'max_mark' => 11, 'grade' => 'E', 'remark' => 'Pass'],
            ['min_mark' => 0, 'max_mark' => 9, 'grade' => 'F', 'remark' => 'Fail'],
        ];

        foreach ($scales as $scale) {
            GradingScale::firstOrCreate(
                [
                    'min_mark' => $scale['min_mark'],
                    'max_mark' => $scale['max_mark'],
                ],
                $scale
            );
        }
    }
}
