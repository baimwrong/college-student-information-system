<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_section_id',
        'term_id',
        'status',
        'assignments_score',
        'midterm_score',
        'final_score',
        'total_score',
        'grade_point',
        'letter_grade',
        'grade_status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'assignments_score' => 'decimal:2',
            'midterm_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'grade_point' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Compute total score, letter grade, and grade points
     * Based on default weights: Assignments (20%), Midterm (35%), Final (45%)
     * or custom sum if scores already scaled.
     */
    public function computeGrade(bool $save = true): void
    {
        $hasScores = false;
        $total = 0;

        if ($this->assignments_score !== null || $this->midterm_score !== null || $this->final_score !== null) {
            $hasScores = true;
            $a = (float) ($this->assignments_score ?? 0);
            $m = (float) ($this->midterm_score ?? 0);
            $f = (float) ($this->final_score ?? 0);

            // Standard composition: Assignments (20%), Midterm (35%), Final Exam (45%)
            $total = ($a * 0.20) + ($m * 0.35) + ($f * 0.45);
        }

        if ($hasScores) {
            $total = round($total, 2);
            $this->total_score = $total;

            if ($total >= 93) {
                $this->letter_grade = 'A';
                $this->grade_point = 4.00;
            } elseif ($total >= 90) {
                $this->letter_grade = 'A-';
                $this->grade_point = 3.70;
            } elseif ($total >= 87) {
                $this->letter_grade = 'B+';
                $this->grade_point = 3.30;
            } elseif ($total >= 83) {
                $this->letter_grade = 'B';
                $this->grade_point = 3.00;
            } elseif ($total >= 80) {
                $this->letter_grade = 'B-';
                $this->grade_point = 2.70;
            } elseif ($total >= 77) {
                $this->letter_grade = 'C+';
                $this->grade_point = 2.30;
            } elseif ($total >= 73) {
                $this->letter_grade = 'C';
                $this->grade_point = 2.00;
            } elseif ($total >= 70) {
                $this->letter_grade = 'C-';
                $this->grade_point = 1.70;
            } elseif ($total >= 60) {
                $this->letter_grade = 'D';
                $this->grade_point = 1.00;
            } else {
                $this->letter_grade = 'F';
                $this->grade_point = 0.00;
            }

            if ($save) {
                $this->save();
                if ($this->grade_status === 'published' && $this->student) {
                    $this->student->recalculateGpa();
                }
            }
        }
    }
}
