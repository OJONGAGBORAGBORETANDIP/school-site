<?php

namespace Database\Seeders;

use App\Models\GradingScale;
use Illuminate\Database\Seeder;

class GradingScaleSeeder extends Seeder
{
    /**
     * A–F grading scale based on total marks out of 100.
     * Percentage ranges: A 80–100%, B 70–79%, C 60–69%, D 50–59%, E 40–49%, F 0–39%.
     */
    public function run(): void
    {
        $scales = [
            ['min_mark' => 80, 'max_mark' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
            ['min_mark' => 70, 'max_mark' => 79, 'grade' => 'B', 'remark' => 'Very Good'],
            ['min_mark' => 60, 'max_mark' => 69, 'grade' => 'C', 'remark' => 'Good'],
            ['min_mark' => 50, 'max_mark' => 59, 'grade' => 'D', 'remark' => 'Credit'],
            ['min_mark' => 40, 'max_mark' => 49, 'grade' => 'E', 'remark' => 'Pass'],
            ['min_mark' => 0, 'max_mark' => 39, 'grade' => 'F', 'remark' => 'Fail'],
        ];

        GradingScale::query()->delete();

        foreach ($scales as $scale) {
            GradingScale::create($scale);
        }
    }
}
