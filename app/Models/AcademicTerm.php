<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'academic_year',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'term_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'term_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
