<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Enrollment $enrollment): void {
            if (filled($enrollment->class_level) || ! $enrollment->class_section_id) {
                return;
            }

            $level = ClassSection::query()
                ->whereKey($enrollment->class_section_id)
                ->with('schoolClass')
                ->first()
                ?->schoolClass
                ?->level;

            if ($level !== null) {
                $enrollment->class_level = $level;
            }
        });
    }

    protected $fillable = [
        'student_id',
        'class_section_id',
        'school_year_id',
        'class_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function termReports(): HasMany
    {
        return $this->hasMany(TermReport::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function promotionDecisions(): HasMany
    {
        return $this->hasMany(PromotionDecision::class);
    }
}
