<?php

namespace Database\Seeders;

use App\Models\AcademicArea;
use App\Models\Course;
use App\Models\Parallel;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // 1. Academic Areas
        $areasData = [
            ['name' => 'Ciencias Exactas', 'code' => 'AREA_EXACTAS'],
            ['name' => 'Lengua y Comunicación', 'code' => 'AREA_LENGUA'],
            ['name' => 'Ciencias Naturales', 'code' => 'AREA_CNATURALES'],
            ['name' => 'Ciencias Sociales', 'code' => 'AREA_CSOCIALES'],
            ['name' => 'Educación Técnica', 'code' => 'AREA_TECNICA'],
        ];

        $areas = [];
        foreach ($areasData as $area) {
            $areas[$area['code']] = AcademicArea::firstOrCreate(
                ['code' => $area['code']],
                ['name' => $area['name'], 'is_active' => true]
            );
        }

        // 2. Subjects
        $subjectsData = [
            ['name' => 'Matemáticas', 'code' => 'SUBJ_MAT', 'area' => 'AREA_EXACTAS'],
            ['name' => 'Lengua y Literatura', 'code' => 'SUBJ_LIT', 'area' => 'AREA_LENGUA'],
            ['name' => 'Física', 'code' => 'SUBJ_FIS', 'area' => 'AREA_EXACTAS'],
            ['name' => 'Química', 'code' => 'SUBJ_QUI', 'area' => 'AREA_CNATURALES'],
            ['name' => 'Historia', 'code' => 'SUBJ_HIS', 'area' => 'AREA_CSOCIALES'],
            ['name' => 'Informática', 'code' => 'SUBJ_INF', 'area' => 'AREA_TECNICA'],
        ];

        foreach ($subjectsData as $subj) {
            $area = $areas[$subj['area']] ?? null;
            Subject::firstOrCreate(
                ['name' => $subj['name']],
                [
                    'code' => $subj['code'],
                    'academic_area_id' => $area ? $area->id : null,
                    'is_active' => true,
                ]
            );
        }

        // 3. Courses
        $coursesData = [
            'Octavo EGB',
            'Noveno EGB',
            'Décimo EGB',
            'Primer Curso BGU',
            'Segundo Curso BGU',
            'Tercer Curso BGU',
        ];

        foreach ($coursesData as $cName) {
            Course::firstOrCreate(
                ['name' => $cName],
                ['is_active' => true]
            );
        }

        // 4. Parallels
        $parallelsData = [
            'Paralelo A',
            'Paralelo B',
            'Paralelo C',
        ];

        foreach ($parallelsData as $pName) {
            Parallel::firstOrCreate(
                ['name' => $pName],
                ['is_active' => true]
            );
        }

        // 5. Teaching Assignments for demo teacher
        $teacher = User::where('email', 'docente@example.com')->first();
        $subject = Subject::where('name', 'Matemáticas')->first();
        $course = Course::where('name', 'Primer Curso BGU')->first();
        $parallel = Parallel::where('name', 'Paralelo A')->first();

        if ($teacher && $subject && $course && $parallel) {
            TeachingAssignment::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'course_id' => $course->id,
                    'parallel_id' => $parallel->id,
                ],
                [
                    'is_active' => true,
                ]
            );
        }
    }
}
