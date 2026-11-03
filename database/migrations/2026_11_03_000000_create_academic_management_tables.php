<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_number')->unique();
            $table->string('programme');
            $table->unsignedTinyInteger('year');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('lecturer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('staff_number')->unique();
            $table->string('department');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('lecturer_id')->nullable()->constrained('lecturer_profiles')->nullOnDelete();
            $table->string('schedule');
            $table->unsignedSmallInteger('capacity');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('registered_at');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('lecturer_profiles');
        Schema::dropIfExists('student_profiles');
    }
};
