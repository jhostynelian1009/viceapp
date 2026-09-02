<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create or retrieve technical placeholder Area (inactive)
        $existingArea = DB::table('academic_areas')->where('code', 'PENDIENTE_AREA')->first();
        if ($existingArea) {
            $areaId = $existingArea->id;
        } else {
            $areaId = DB::table('academic_areas')->insertGetId([
                'name' => 'Pendiente de clasificación',
                'code' => 'PENDIENTE_AREA',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Associate existing subjects to this technical area
        DB::table('subjects')
            ->whereNull('academic_area_id')
            ->update([
                'academic_area_id' => $areaId,
            ]);

        // 2. Create or retrieve technical placeholder Course (inactive)
        $existingCourse = DB::table('courses')->where('name', 'Pendiente de clasificación')->first();
        if ($existingCourse) {
            $courseId = $existingCourse->id;
        } else {
            $courseId = DB::table('courses')->insertGetId([
                'name' => 'Pendiente de clasificación',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Create or retrieve technical placeholder Parallel (inactive)
        $existingParallel = DB::table('parallels')->where('name', 'Pendiente')->first();
        if ($existingParallel) {
            $parallelId = $existingParallel->id;
        } else {
            $parallelId = DB::table('parallels')->insertGetId([
                'name' => 'Pendiente',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Backfill historical plannings
        $plannings = DB::table('plannings')->get();

        foreach ($plannings as $planning) {
            $teacherId = $planning->user_id;
            $subjectId = $planning->subject_id;

            if ($teacherId && $subjectId) {
                // Find or create a technical teaching assignment (inactive)
                $assignment = DB::table('teaching_assignments')
                    ->where('teacher_id', $teacherId)
                    ->where('subject_id', $subjectId)
                    ->where('course_id', $courseId)
                    ->where('parallel_id', $parallelId)
                    ->first();

                if (!$assignment) {
                    $assignmentId = DB::table('teaching_assignments')->insertGetId([
                        'teacher_id' => $teacherId,
                        'subject_id' => $subjectId,
                        'course_id' => $courseId,
                        'parallel_id' => $parallelId,
                        'is_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $assignmentId = $assignment->id;
                }

                // Update the planning record
                DB::table('plannings')
                    ->where('id', $planning->id)
                    ->update([
                        'assignment_id' => $assignmentId,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set assignment_id back to null
        DB::table('plannings')->update(['assignment_id' => null]);

        // Delete teaching assignments created during migration
        DB::table('teaching_assignments')->truncate();

        // Delete placeholders
        DB::table('parallels')->where('name', 'Pendiente')->delete();
        DB::table('courses')->where('name', 'Pendiente de clasificación')->delete();

        // Restore subjects' academic_area_id to null
        DB::table('subjects')->update(['academic_area_id' => null]);
        DB::table('academic_areas')->where('code', 'PENDIENTE_AREA')->delete();
    }
};
