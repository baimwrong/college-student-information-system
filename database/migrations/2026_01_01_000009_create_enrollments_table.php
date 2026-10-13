<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_section_id')->constrained('course_sections')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->string('status', 20)->default('enrolled'); // enrolled, dropped, completed, withdrawn
            $table->decimal('assignments_score', 5, 2)->nullable(); // 0 to 100 or weighted
            $table->decimal('midterm_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->decimal('grade_point', 3, 2)->nullable(); // 0.00 - 4.00
            $table->string('letter_grade', 5)->nullable(); // A, A-, B+, B, B-, C+, C, D, F
            $table->string('grade_status', 20)->default('draft'); // draft, submitted, published
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
