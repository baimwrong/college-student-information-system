<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('degree_level', 30)->default('undergraduate'); // undergraduate, postgraduate, diploma, doctorate
            $table->integer('duration_years')->default(4);
            $table->integer('total_credits_required')->default(120);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_programs');
    }
};
