<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'other_names',
        'date_of_birth',
        'gender',
        'admission_number',
        'admission_date',
        'address',
        'photo_path',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    /**
     * @return BelongsToMany<ParentModel>
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'parent_student')->withTimestamps();
    }

    /**
     * @return HasMany<Enrollment>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}

