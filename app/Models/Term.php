<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_id',
        'number',
        'name',
        'starts_at',
        'ends_at',
        'results_published_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'results_published_at' => 'date',
        'is_active' => 'boolean',
    ];

    /** Scope: only the active term (for data entry). */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @return BelongsTo<SchoolYear, Term>
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}

