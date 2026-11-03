<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_number')->nullable()->unique();
            $table->string('programme')->nullable();
            $table->unsignedTinyInteger('year_of_study')->nullable();
            $table->string('student_status')->nullable();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('lecturer');
            $table->string('schedule');
            $table->unsignedSmallInteger('capacity');
            $table->unsignedSmallInteger('enrolled')->default(0);
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_number']);
            $table->dropColumn([
                'student_number',
                'programme',
                'year_of_study',
                'student_status',
            ]);
        });
    }
};
