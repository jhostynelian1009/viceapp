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
        // 1. Add constraint to subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreign('academic_area_id')
                ->references('id')
                ->on('academic_areas')
                ->onDelete('restrict');
        });

        // 2. Add constraints to teaching_assignments
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->onDelete('restrict');

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('restrict');

            $table->foreign('parallel_id')
                ->references('id')
                ->on('parallels')
                ->onDelete('restrict');
        });

        // 3. Add constraint to plannings
        Schema::table('plannings', function (Blueprint $table) {
            $table->foreign('assignment_id')
                ->references('id')
                ->on('teaching_assignments')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['parallel_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['academic_area_id']);
        });
    }
};
