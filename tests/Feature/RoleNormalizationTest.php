<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleNormalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test conversion of each legacy role variant to canonical role names.
     */
    public function test_legacy_role_variants_are_normalized(): void
    {
        $legacyNames = [
            'Docente',
            'docente',
            'Secretaría',
            'Secretaria',
            'secretaria',
            'Vicerrector',
            'vicerrector',
            'Vicerrectorado',
            'vicerrectorado',
        ];

        foreach ($legacyNames as $legacyName) {
            Role::firstOrCreate(['name' => $legacyName, 'guard_name' => 'web']);
        }

        $migration = include database_path('migrations/2026_08_29_000001_normalize_role_names.php');
        $migration->up();

        $roles = Role::all()->pluck('name')->sort()->values()->toArray();

        $this->assertEquals(['docente', 'secretaria', 'vicerrectorado'], $roles);
    }

    /**
     * Test coexistence between legacy role and canonical role with pivot preservation.
     */
    public function test_coexistence_and_model_has_roles_preservation(): void
    {
        $legacyRole = Role::create(['name' => 'Vicerrector', 'guard_name' => 'web']);
        $canonicalRole = Role::create(['name' => 'vicerrectorado', 'guard_name' => 'web']);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->assignRole($legacyRole);
        $user2->assignRole($canonicalRole);

        $migration = include database_path('migrations/2026_08_29_000001_normalize_role_names.php');
        $migration->up();

        $this->assertTrue($user1->fresh()->hasRole('vicerrectorado'));
        $this->assertTrue($user2->fresh()->hasRole('vicerrectorado'));

        $countPivots = DB::table('model_has_roles')
            ->where('role_id', $canonicalRole->id)
            ->count();

        $this->assertEquals(2, $countPivots);
    }

    /**
     * Test preservation of role_has_permissions and no duplicate pivot assignments.
     */
    public function test_role_has_permissions_preservation_and_no_duplicates(): void
    {
        $permission = Permission::create(['name' => 'review-plannings', 'guard_name' => 'web']);
        $legacyRole = Role::create(['name' => 'Vicerrector', 'guard_name' => 'web']);
        $canonicalRole = Role::create(['name' => 'vicerrectorado', 'guard_name' => 'web']);

        $legacyRole->givePermissionTo($permission);
        $canonicalRole->givePermissionTo($permission);

        $migration = include database_path('migrations/2026_08_29_000001_normalize_role_names.php');
        $migration->up();

        $this->assertEquals(1, Role::where('name', 'vicerrectorado')->count());

        $pivots = DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->get();

        $this->assertCount(1, $pivots);
        $this->assertEquals($canonicalRole->id, $pivots->first()->role_id);
    }

    /**
     * Test idempotent execution of migration up().
     */
    public function test_migration_is_idempotent(): void
    {
        Role::create(['name' => 'docente', 'guard_name' => 'web']);
        Role::create(['name' => 'secretaria', 'guard_name' => 'web']);
        Role::create(['name' => 'vicerrectorado', 'guard_name' => 'web']);

        $migration = include database_path('migrations/2026_08_29_000001_normalize_role_names.php');

        // Run once
        $migration->up();
        $countFirstRun = Role::count();

        // Run again
        $migration->up();
        $countSecondRun = Role::count();

        $this->assertEquals(3, $countFirstRun);
        $this->assertEquals(3, $countSecondRun);
    }

    /**
     * Test that down() throws explicit RuntimeException indicating irreversibility.
     */
    public function test_down_throws_runtime_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La migración de normalización de roles realiza una consolidación irreversible');

        $migration = include database_path('migrations/2026_08_29_000001_normalize_role_names.php');
        $migration->down();
    }
}
