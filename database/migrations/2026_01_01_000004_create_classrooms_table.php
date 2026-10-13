<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 50)->unique();
            $table->string('building', 100);
            $table->integer('capacity')->default(40);
            $table->string('type', 50)->default('lecture_hall'); // lecture_hall, lab, seminar, auditorium
            $table->string('status', 20)->default('available'); // available, maintenance
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
