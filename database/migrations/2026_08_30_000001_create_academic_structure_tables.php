<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create academic_areas table
        Schema::create('academic_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create courses table
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Create parallels table
        Schema::create('parallels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Modify subjects table (add columns as nullable initially)
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_area_id')->nullable();
            $table->string('code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
        });

        // 5. Create teaching_assignments table (without foreign keys initially)
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('parallel_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id', 'course_id', 'parallel_id'], 'teacher_subject_course_parallel_unique');
        });

        // 6. Modify plannings table (add assignment_id, week_start, week_end as nullable)
        Schema::table('plannings', function (Blueprint $table) {
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->date('week_start')->nullable();
            $table->date('week_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            Schema::disableForeignKeyConstraints();
        }

        Schema::table('plannings', function (Blueprint $table) {
            $table->dropColumn(['assignment_id', 'week_start', 'week_end']);
        });

        Schema::dropIfExists('teaching_assignments');

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['academic_area_id', 'code', 'is_active']);
        });

        Schema::dropIfExists('parallels');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('academic_areas');

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            Schema::enableForeignKeyConstraints();
        }
    }
};
