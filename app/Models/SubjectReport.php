<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_report_id',
        'subject_id',
        'ca_mark',
        'exam_mark',
        'total_mark',
        'grade',
        'remark',
        'teacher_comment',
        'submitted_at',
        'ca_submitted_at',
        'exam_submitted_at',
        'ca_approved_at',
        'ca_rejected_at',
        'ca_rejection_reason',
        'exam_approved_at',
        'exam_rejected_at',
        'exam_rejection_reason',
    ];

    /**
     * Ensure empty strings are stored as null for decimal columns (MySQL rejects '').
     */
    public function setCaMarkAttribute($value): void
    {
        $this->attributes['ca_mark'] = ($value === '' || $value === null) ? null : $value;
    }

    public function setExamMarkAttribute($value): void
    {
        $this->attributes['exam_mark'] = ($value === '' || $value === null) ? null : $value;
    }

    public function setTotalMarkAttribute($value): void
    {
        $this->attributes['total_mark'] = ($value === '' || $value === null) ? null : $value;
    }

    protected $casts = [
        'ca_mark' => 'decimal:2',
        'exam_mark' => 'decimal:2',
        'total_mark' => 'decimal:2',
        'submitted_at' => 'datetime',
        'ca_submitted_at' => 'datetime',
        'exam_submitted_at' => 'datetime',
        'ca_approved_at' => 'datetime',
        'ca_rejected_at' => 'datetime',
        'exam_approved_at' => 'datetime',
        'exam_rejected_at' => 'datetime',
    ];

    /** Whether CA has been submitted and not rejected (pending approval). */
    public function isCaPending(): bool
    {
        return $this->ca_submitted_at !== null && $this->ca_rejected_at === null && $this->ca_approved_at === null;
    }

    /** Whether Exam has been submitted and not rejected (pending approval). */
    public function isExamPending(): bool
    {
        return $this->exam_submitted_at !== null && $this->exam_rejected_at === null && $this->exam_approved_at === null;
    }

    /** Teacher can edit CA only if CA has not been approved for this subject. Once approved, read-only. */
    public function canEditCa(): bool
    {
        if ($this->ca_approved_at !== null) {
            return false;
        }
        return $this->ca_submitted_at === null || $this->ca_rejected_at !== null;
    }

    /** Teacher can edit Exam only if Exam has not been approved for this subject. Once approved, read-only. */
    public function canEditExam(): bool
    {
        if ($this->exam_approved_at !== null) {
            return false;
        }
        return $this->exam_submitted_at === null || $this->exam_rejected_at !== null;
    }

    /** Whether this subject report has been submitted for head teacher approval (legacy: either component). */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null || $this->ca_submitted_at !== null || $this->exam_submitted_at !== null;
    }

    public function termReport(): BelongsTo
    {
        return $this->belongsTo(TermReport::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Primary school scoring:
     * - Sequence is out of 20
     * - Exam is out of 20
     * - Final subject score (total_mark) is the average of both, out of 20.
     * Grade scale remains 0-100, so we map out-of-20 to percentage by multiplying by 5.
     */
    public function calculateTotal(): void
    {
        if ($this->ca_mark !== null && $this->exam_mark !== null) {
            $this->total_mark = ((float) $this->ca_mark + (float) $this->exam_mark) / 2;
            $gradeInfo = \App\Models\GradingScale::getGradeForMark((float) $this->total_mark);
            if ($gradeInfo) {
                $this->grade = $gradeInfo['grade'];
                $this->remark = $gradeInfo['remark'];
            }
        } else {
            $this->total_mark = null;
            $this->grade = null;
            $this->remark = null;
        }
    }
}
