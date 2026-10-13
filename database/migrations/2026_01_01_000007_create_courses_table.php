<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('code', 30)->unique(); // e.g. CS101, MATH201
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('credits')->default(3);
            $table->integer('level')->default(100); // 100, 200, 300, 400
            $table->integer('lecture_hours')->default(3);
            $table->integer('lab_hours')->default(0);
            $table->string('prerequisites')->nullable(); // e.g. "CS101, MATH101"
            $table->string('status', 20)->default('active'); // active, archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
