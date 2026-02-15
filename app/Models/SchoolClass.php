<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'level',
    ];

    /**
     * @return HasMany<ClassSection>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }
}

