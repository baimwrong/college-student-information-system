<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'program_id',
        'student_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'admission_date',
        'current_semester',
        'academic_status',
        'cgpa',
        'total_credits_earned',
        'transcript_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->transcript_token)) {
                $student->transcript_token = 'TR-'.strtoupper(Str::random(10));
            }
        });
    }

    public function getTranscriptTokenAttribute(): string
    {
        if (empty($this->attributes['transcript_token'])) {
            $token = 'TR-'.strtoupper(substr(md5($this->student_id.config('app.key')), 0, 10));
            $this->attributes['transcript_token'] = $token;
            if ($this->exists) {
                $this->saveQuietly();
            }
        }

        return $this->attributes['transcript_token'];
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'cgpa' => 'decimal:2',
            'current_semester' => 'integer',
            'total_credits_earned' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Recalculate CGPA and total earned credits based on published grades
     */
    public function recalculateGpa(): void
    {
        $publishedEnrollments = $this->enrollments()
            ->with(['courseSection.course', 'term'])
            ->where('grade_status', 'published')
            ->whereNotNull('grade_point')
            ->get();

        // Group enrollments by course_id to handle course retakes (latest attempt policy)
        $courseEnrollments = [];
        foreach ($publishedEnrollments as $enrollment) {
            $courseId = $enrollment->courseSection->course_id ?? null;
            if (! $courseId) {
                continue;
            }

            if (! isset($courseEnrollments[$courseId])) {
                $courseEnrollments[$courseId] = $enrollment;
            } else {
                $existing = $courseEnrollments[$courseId];
                $existingDate = $existing->term->start_date ?? null;
                $currentDate = $enrollment->term->start_date ?? null;

                if ($currentDate && $existingDate) {
                    if ($currentDate > $existingDate || ($currentDate == $existingDate && $enrollment->id > $existing->id)) {
                        $courseEnrollments[$courseId] = $enrollment;
                    }
                } elseif ($enrollment->id > $existing->id) {
                    $courseEnrollments[$courseId] = $enrollment;
                }
            }
        }

        $totalPoints = 0;
        $totalCredits = 0;
        $earnedCredits = 0;

        foreach ($courseEnrollments as $enrollment) {
            $credits = $enrollment->courseSection->course->credits ?? 0;
            if ($credits > 0) {
                $totalPoints += ($enrollment->grade_point * $credits);
                $totalCredits += $credits;

                // Credit earned if passing (grade point > 0.0)
                if ($enrollment->grade_point > 0.0) {
                    $earnedCredits += $credits;
                }
            }
        }

        $cgpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00;

        $this->update([
            'cgpa' => $cgpa,
            'total_credits_earned' => $earnedCredits,
        ]);
    }
}
