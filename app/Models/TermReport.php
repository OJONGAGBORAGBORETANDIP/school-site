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
        'submitted_at',
    ];

    protected $casts = [
        'average' => 'decimal:2',
        'class_average' => 'decimal:2',
        'is_approved_by_headteacher' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    /** Whether this term report is still draft (editable by teacher). */
    public function isDraft(): bool
    {
        return $this->submitted_at === null;
    }

    /** Whether this term report has been submitted for head teacher approval. */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /** Whether all subject reports have each component either not entered or approved (report card viewable by parent). */
    public function isFullyApproved(): bool
    {
        $reports = $this->subjectReports;
        if ($reports->isEmpty()) {
            return false;
        }
        return $reports->every(function ($sr) {
            $caOk = $sr->ca_mark === null || $sr->ca_approved_at !== null;
            $examOk = $sr->exam_mark === null || $sr->exam_approved_at !== null;
            return $caOk && $examOk;
        });
    }

    /** Whether at least one subject has an approved CA or Exam (parent can see interim marks). */
    public function hasAnyApprovedMarks(): bool
    {
        return $this->subjectReports->contains(function ($sr) {
            return $sr->ca_approved_at !== null || $sr->exam_approved_at !== null;
        });
    }

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
