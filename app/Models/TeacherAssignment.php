<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_section_id',
        'subject_id',
    ];

    /**
     * @return BelongsTo<User, TeacherAssignment>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return BelongsTo<ClassSection, TeacherAssignment>
     */
    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    /**
     * @return BelongsTo<Subject, TeacherAssignment>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}

