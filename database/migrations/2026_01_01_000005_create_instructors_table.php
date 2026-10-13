<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('employee_id', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('designation', 100)->default('Lecturer'); // Professor, Associate Professor, Assistant Professor, Lecturer, etc.
            $table->string('specialization', 150)->nullable();
            $table->string('office_room', 50)->nullable();
            $table->date('hire_date')->nullable();
            $table->string('status', 20)->default('active'); // active, on_leave, retired, terminated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
