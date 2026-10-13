<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'code',
        'title',
        'description',
        'credits',
        'level',
        'lecture_hours',
        'lab_hours',
        'prerequisites',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'level' => 'integer',
            'lecture_hours' => 'integer',
            'lab_hours' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function getFullTitleAttribute(): string
    {
        return "{$this->code} - {$this->title}";
    }
}
