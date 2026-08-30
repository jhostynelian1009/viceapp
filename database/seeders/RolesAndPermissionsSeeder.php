<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles
        Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'vicerrectorado', 'guard_name' => 'web']);
    }
}
