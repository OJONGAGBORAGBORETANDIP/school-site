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
    ];

    protected $casts = [
        'ca_mark' => 'decimal:2',
        'exam_mark' => 'decimal:2',
        'total_mark' => 'decimal:2',
    ];

    public function termReport(): BelongsTo
    {
        return $this->belongsTo(TermReport::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Calculate total mark from CA and Exam marks
     */
    public function calculateTotal(): void
    {
        $caWeight = 0.4; // 40% CA, 60% Exam (adjustable)
        $examWeight = 0.6;

        if ($this->ca_mark !== null && $this->exam_mark !== null) {
            $this->total_mark = ($this->ca_mark * $caWeight) + ($this->exam_mark * $examWeight);
            
            // Get grade and remark from grading scale
            $gradeInfo = \App\Models\GradingScale::getGradeForMark($this->total_mark);
            if ($gradeInfo) {
                $this->grade = $gradeInfo['grade'];
                $this->remark = $gradeInfo['remark'];
            }
        }
    }
}
