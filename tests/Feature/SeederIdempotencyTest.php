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

        // Second run
        $this->seed(DatabaseSeeder::class);

        $userCountRun2 = User::count();
        $roleCountRun2 = Role::count();
        $subjectCountRun2 = Subject::count();

        $this->assertEquals(3, $userCountRun1);
        $this->assertEquals(3, $userCountRun2);

        $this->assertEquals(3, $roleCountRun1);
        $this->assertEquals(3, $roleCountRun2);

        $this->assertEquals(8, $subjectCountRun1);
        $this->assertEquals(8, $subjectCountRun2);
    }
}
