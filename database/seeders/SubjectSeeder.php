<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            'Matemáticas',
            'Lengua y Literatura',
            'Ciencias Naturales',
            'Ciencias Sociales',
            'Educación Física',
            'Educación Cultural y Artística',
            'Inglés',
            'Informática',
        ];

        foreach ($subjects as $subject) {
            \App\Models\Subject::firstOrCreate(['name' => $subject]);
        }
    }
}
