<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BehaviourRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_report_id',
        'aspect',
        'rating',
        'comment',
    ];

    public function termReport(): BelongsTo
    {
        return $this->belongsTo(TermReport::class);
    }
}
