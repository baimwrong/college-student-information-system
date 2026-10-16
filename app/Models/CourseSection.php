<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'term_id',
        'instructor_id',
        'classroom_id',
        'section_number',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
        'enrolled_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'enrolled_count' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function hasSeatsAvailable(): bool
    {
        return $this->enrolled_count < $this->capacity;
    }

    public function getScheduleSummaryAttribute(): string
    {
        if (! $this->day_of_week && ! $this->start_time) {
            return 'TBA';
        }

        return "{$this->day_of_week} ({$this->start_time} - {$this->end_time})";
    }
}
