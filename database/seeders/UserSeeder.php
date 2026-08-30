<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Solo crear usuarios demo en entorno local o de pruebas
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        // Crear roles si no existen
        $docenteRole = Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        $secretariaRole = Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        $vicerrectorRole = Role::firstOrCreate(['name' => 'vicerrectorado', 'guard_name' => 'web']);

        // Crear o actualizar usuario docente
        $docente = User::firstOrCreate(
            ['email' => 'docente@example.com'],
            [
                'name' => 'Usuario Docente',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $docente->syncRoles([$docenteRole]);

        // Crear o actualizar usuario secretaria
        $secretaria = User::firstOrCreate(
            ['email' => 'secretaria@example.com'],
            [
                'name' => 'Usuario Secretaria',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $secretaria->syncRoles([$secretariaRole]);

        // Crear o actualizar usuario vicerrector
        $vicerrector = User::firstOrCreate(
            ['email' => 'vicerrector@example.com'],
            [
                'name' => 'Usuario Vicerrector',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $vicerrector->syncRoles([$vicerrectorRole]);
    }
}
