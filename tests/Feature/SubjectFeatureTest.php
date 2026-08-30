<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'docente']);
        Role::firstOrCreate(['name' => 'secretaria']);
        Role::firstOrCreate(['name' => 'vicerrectorado']);
    }

    public function test_vicerrectorado_and_secretaria_can_access_and_manage_subjects()
    {
        $vicerrector = User::factory()->create(['is_active' => true]);
        $vicerrector->assignRole('vicerrectorado');

        $secretaria = User::factory()->create(['is_active' => true]);
        $secretaria->assignRole('secretaria');

        $subject = Subject::create(['name' => 'Matemáticas']);

        // Vicerrectorado access
        $this->actingAs($vicerrector);
        $this->get(route('subjects.index'))->assertStatus(200)->assertSee('Matemáticas');

        $this->post(route('subjects.store'), ['name' => 'Física'])->assertRedirect(route('subjects.index'));
        $this->assertDatabaseHas('subjects', ['name' => 'Física']);

        // Secretaría access
        $this->actingAs($secretaria);
        $this->get(route('subjects.index'))->assertStatus(200)->assertSee('Física');

        $this->put(route('subjects.update', $subject), ['name' => 'Matemáticas Avanzadas'])->assertRedirect(route('subjects.index'));
        $this->assertDatabaseHas('subjects', ['name' => 'Matemáticas Avanzadas']);
    }

    public function test_docente_cannot_access_subjects()
    {
        $docente = User::factory()->create(['is_active' => true]);
        $docente->assignRole('docente');

        $this->actingAs($docente);

        $this->get(route('subjects.index'))->assertStatus(403);
        $this->post(route('subjects.store'), ['name' => 'Química'])->assertStatus(403);
    }

    public function test_user_without_role_cannot_access_subjects()
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user);

        $this->get(route('subjects.index'))->assertStatus(403);
        $this->post(route('subjects.store'), ['name' => 'Química'])->assertStatus(403);
    }
}
