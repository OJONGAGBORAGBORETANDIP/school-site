<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_mark',
        'max_mark',
        'grade',
        'remark',
    ];

    /**
     * Primary school grading scale on 20.
     * F is any mark below 10.
     *
     * @return array<int, array{min_mark: float, max_mark: float, grade: string, remark: string}>
     */
    public static function primaryScaleOutOf20(): array
    {
        return [
            ['min_mark' => 18.0, 'max_mark' => 20.0, 'grade' => 'A', 'remark' => 'Excellent'],
            ['min_mark' => 16.0, 'max_mark' => 17.99, 'grade' => 'B', 'remark' => 'Very Good'],
            ['min_mark' => 14.0, 'max_mark' => 15.99, 'grade' => 'C', 'remark' => 'Good'],
            ['min_mark' => 12.0, 'max_mark' => 13.99, 'grade' => 'D', 'remark' => 'Credit'],
            ['min_mark' => 10.0, 'max_mark' => 11.99, 'grade' => 'E', 'remark' => 'Pass'],
            ['min_mark' => 0.0, 'max_mark' => 9.99, 'grade' => 'F', 'remark' => 'Fail'],
        ];
    }

    /**
     * Get grade and remark for a given mark (out of 20).
     */
    public static function getGradeForMark(float $mark): ?array
    {
        foreach (self::primaryScaleOutOf20() as $scale) {
            if ($mark >= $scale['min_mark'] && $mark <= $scale['max_mark']) {
                return [
                    'grade' => $scale['grade'],
                    'remark' => $scale['remark'],
                ];
            }
        }

        return null;
    }
}
