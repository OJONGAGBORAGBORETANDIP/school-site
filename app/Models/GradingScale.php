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
     * Get grade and remark for a given mark
     */
    public static function getGradeForMark(float $mark): ?array
    {
        $scale = self::where('min_mark', '<=', $mark)
            ->where('max_mark', '>=', $mark)
            ->first();

        return $scale ? [
            'grade' => $scale->grade,
            'remark' => $scale->remark,
        ] : null;
    }
}
