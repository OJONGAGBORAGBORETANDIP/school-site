<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'school_year_id',
        'is_promoted',
        'next_class_label',
        'decision_note',
    ];

    protected $casts = [
        'is_promoted' => 'boolean',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
