<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'term_id',
        'average',
        'position',
        'class_average',
        'class_size',
        'class_teacher_remark',
        'headteacher_remark',
        'is_approved_by_headteacher',
    ];

    protected $casts = [
        'average' => 'decimal:2',
        'class_average' => 'decimal:2',
        'is_approved_by_headteacher' => 'boolean',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function subjectReports(): HasMany
    {
        return $this->hasMany(SubjectReport::class);
    }

    public function behaviourRatings(): HasMany
    {
        return $this->hasMany(BehaviourRating::class);
    }
}
