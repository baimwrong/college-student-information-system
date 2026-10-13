<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('section_number', 20)->default('SEC-A'); // SEC-A, SEC-B
            $table->string('day_of_week', 50)->nullable(); // e.g. Mon / Wed, Tue / Thu
            $table->string('start_time', 10)->nullable(); // e.g. 09:00
            $table->string('end_time', 10)->nullable(); // e.g. 10:30
            $table->integer('capacity')->default(40);
            $table->integer('enrolled_count')->default(0);
            $table->string('status', 20)->default('open'); // open, closed, cancelled
            $table->timestamps();

            $table->unique(['course_id', 'term_id', 'section_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};
