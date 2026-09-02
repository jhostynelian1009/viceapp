<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that executing DatabaseSeeder twice is completely idempotent.
     */
    public function test_database_seeder_is_idempotent(): void
    {
        // First run
        $this->seed(DatabaseSeeder::class);

        $userCountRun1 = User::count();
        $roleCountRun1 = Role::count();
        $subjectCountRun1 = Subject::count();
        $areaCountRun1 = \App\Models\AcademicArea::count();
        $courseCountRun1 = \App\Models\Course::count();
        $parallelCountRun1 = \App\Models\Parallel::count();
        $assignmentCountRun1 = \App\Models\TeachingAssignment::count();

        // Second run
        $this->seed(DatabaseSeeder::class);

        $userCountRun2 = User::count();
        $roleCountRun2 = Role::count();
        $subjectCountRun2 = Subject::count();
        $areaCountRun2 = \App\Models\AcademicArea::count();
        $courseCountRun2 = \App\Models\Course::count();
        $parallelCountRun2 = \App\Models\Parallel::count();
        $assignmentCountRun2 = \App\Models\TeachingAssignment::count();

        $this->assertEquals(3, $userCountRun1);
        $this->assertEquals(3, $userCountRun2);

        $this->assertEquals(3, $roleCountRun1);
        $this->assertEquals(3, $roleCountRun2);

        $this->assertGreaterThan(0, $subjectCountRun1);
        $this->assertEquals($subjectCountRun1, $subjectCountRun2);

        $this->assertGreaterThan(0, $areaCountRun1);
        $this->assertEquals($areaCountRun1, $areaCountRun2);

        $this->assertGreaterThan(0, $courseCountRun1);
        $this->assertEquals($courseCountRun1, $courseCountRun2);

        $this->assertGreaterThan(0, $parallelCountRun1);
        $this->assertEquals($parallelCountRun1, $parallelCountRun2);

        $this->assertGreaterThan(0, $assignmentCountRun1);
        $this->assertEquals($assignmentCountRun1, $assignmentCountRun2);
    }

    public function test_academic_structure_seeder_blocked_outside_local_and_testing(): void
    {
        $originalEnv = app()->environment();

        try {
            // Simulate production environment
            app()['env'] = 'production';
            $this->assertEquals('production', app()->environment());

            $areaCountBefore = \App\Models\AcademicArea::count();
            $courseCountBefore = \App\Models\Course::count();
            $parallelCountBefore = \App\Models\Parallel::count();
            $assignmentCountBefore = \App\Models\TeachingAssignment::count();

            // Run AcademicStructureSeeder in production environment directly
            (new \Database\Seeders\AcademicStructureSeeder)->run();

            // Assert no demo academic structure models were created
            $this->assertEquals($areaCountBefore, \App\Models\AcademicArea::count());
            $this->assertEquals($courseCountBefore, \App\Models\Course::count());
            $this->assertEquals($parallelCountBefore, \App\Models\Parallel::count());
            $this->assertEquals($assignmentCountBefore, \App\Models\TeachingAssignment::count());
        } finally {
            // Restore original environment
            app()['env'] = $originalEnv;
        }

        $this->assertEquals($originalEnv, app()->environment());
    }
}
