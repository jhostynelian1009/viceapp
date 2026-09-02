<?php

namespace Tests\Feature;

use App\Models\AcademicArea;
use App\Models\Course;
use App\Models\Parallel;
use App\Models\Planning;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    protected User $secretaria;

    protected User $docenteA;

    protected User $docenteB;

    protected AcademicArea $area;

    protected Subject $subject;

    protected Course $course;

    protected Parallel $parallel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->secretaria = User::factory()->create(['is_active' => true]);
        $this->secretaria->assignRole('secretaria');

        $this->docenteA = User::factory()->create(['is_active' => true]);
        $this->docenteA->assignRole('docente');

        $this->docenteB = User::factory()->create(['is_active' => true]);
        $this->docenteB->assignRole('docente');

        $this->area = AcademicArea::create(['name' => 'Ciencias Exactas', 'code' => 'CEX', 'is_active' => true]);
        $this->subject = Subject::create([
            'name' => 'Física I',
            'code' => 'FIS101',
            'academic_area_id' => $this->area->id,
            'is_active' => true,
        ]);
        $this->course = Course::create(['name' => 'Primer Año Bachillerato', 'is_active' => true]);
        $this->parallel = Parallel::create(['name' => 'Paralelo A', 'is_active' => true]);

        Storage::fake('private_plannings');
    }

    public function test_secretaria_can_create_valid_teaching_assignment()
    {
        $this->actingAs($this->secretaria);

        $response = $this->post(route('teaching-assignments.store'), [
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
        ]);

        $response->assertRedirect(route('teaching-assignments.index'));
        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);
    }

    public function test_duplicate_teaching_assignment_is_rejected()
    {
        $this->actingAs($this->secretaria);

        // First assignment
        TeachingAssignment::create([
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        // Attempt duplicate
        $response = $this->post(route('teaching-assignments.store'), [
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
        ]);

        $response->assertSessionHasErrors(['teacher_id']);
    }

    public function test_inactive_catalogs_are_rejected_in_teaching_assignment_creation()
    {
        $this->actingAs($this->secretaria);

        // Inactive teacher
        $inactiveTeacher = User::factory()->create(['is_active' => false]);
        $inactiveTeacher->assignRole('docente');

        $response = $this->post(route('teaching-assignments.store'), [
            'teacher_id' => $inactiveTeacher->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
        ]);
        $response->assertSessionHasErrors(['teacher_id']);

        // Inactive subject
        $inactiveSubject = Subject::create([
            'name' => 'Historia Inactiva',
            'code' => 'HIST01',
            'academic_area_id' => $this->area->id,
            'is_active' => false,
        ]);
        $response = $this->post(route('teaching-assignments.store'), [
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $inactiveSubject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
        ]);
        $response->assertSessionHasErrors(['subject_id']);

        // Inactive course
        $inactiveCourse = Course::create(['name' => 'Curso Inactivo', 'is_active' => false]);
        $response = $this->post(route('teaching-assignments.store'), [
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $inactiveCourse->id,
            'parallel_id' => $this->parallel->id,
        ]);
        $response->assertSessionHasErrors(['course_id']);

        // Inactive parallel
        $inactiveParallel = Parallel::create(['name' => 'Paralelo Inactivo', 'is_active' => false]);
        $response = $this->post(route('teaching-assignments.store'), [
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $inactiveParallel->id,
        ]);
        $response->assertSessionHasErrors(['parallel_id']);
    }

    public function test_toggling_teaching_assignment_status()
    {
        $this->actingAs($this->secretaria);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        // Toggle to inactive
        $response = $this->patch(route('teaching-assignments.toggleActive', $assignment));
        $response->assertRedirect(route('teaching-assignments.index'));
        $this->assertFalse((bool) $assignment->fresh()->is_active);

        // Toggle back to active
        $response = $this->patch(route('teaching-assignments.toggleActive', $assignment));
        $response->assertRedirect(route('teaching-assignments.index'));
        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }

    public function test_planning_creation_validations_and_requirements()
    {
        $this->actingAs($this->docenteA);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        $file = $this->createValidPdfFile('planificacion_semanal.pdf');

        // Missing week_start and week_end
        $response = $this->post(route('plannings.store'), [
            'title' => 'Plan de Física',
            'assignment_id' => $assignment->id,
            'file' => $file,
        ]);
        $response->assertSessionHasErrors(['week_start', 'week_end']);

        // Valid planning upload
        $response = $this->post(route('plannings.store'), [
            'title' => 'Plan de Física Semanal',
            'assignment_id' => $assignment->id,
            'week_start' => '2026-08-30',
            'week_end' => '2026-09-03',
            'file' => $file,
        ]);
        $response->assertRedirect(route('plannings.index'));
        $this->assertDatabaseHas('plannings', [
            'title' => 'Plan de Física Semanal',
            'assignment_id' => $assignment->id,
            'subject_id' => $this->subject->id,
            'user_id' => $this->docenteA->id,
        ]);
    }

    public function test_planning_upload_fails_if_assignment_belongs_to_another_teacher()
    {
        $this->actingAs($this->docenteA);

        // Assignment for Docente B
        $assignmentB = TeachingAssignment::create([
            'teacher_id' => $this->docenteB->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        $file = $this->createValidPdfFile('plan.pdf');

        $response = $this->post(route('plannings.store'), [
            'title' => 'Plan Intruder',
            'assignment_id' => $assignmentB->id,
            'week_start' => '2026-08-30',
            'week_end' => '2026-09-03',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['assignment_id']);
        $this->assertDatabaseMissing('plannings', ['title' => 'Plan Intruder']);
    }

    public function test_planning_upload_fails_if_assignment_is_inactive()
    {
        $this->actingAs($this->docenteA);

        $inactiveAssignment = TeachingAssignment::create([
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => false,
        ]);

        $file = $this->createValidPdfFile('plan.pdf');

        $response = $this->post(route('plannings.store'), [
            'title' => 'Plan Inactivo',
            'assignment_id' => $inactiveAssignment->id,
            'week_start' => '2026-08-30',
            'week_end' => '2026-09-03',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['assignment_id']);
        $this->assertDatabaseMissing('plannings', ['title' => 'Plan Inactivo']);
    }

    public function test_historical_migration_preserves_records_ids_and_associations()
    {
        // 1. Setup pre-K-005 historical data state (unclassified subject & null assignment planning)
        $teacher = User::factory()->create(['is_active' => true]);
        $teacher->assignRole('docente');

        $subject = new Subject([
            'name' => 'Materia Histórica 99',
            'academic_area_id' => null,
            'is_active' => true,
        ]);
        $subject->id = 99;
        $subject->save();

        $planning = Planning::create([
            'user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'assignment_id' => null,
            'title' => 'Planificación Histórica Pre-K005',
            'file_path' => 'historical.pdf',
            'status' => 'aprobado',
        ]);

        // 2. Execute K-005 backfill migration up
        $m2 = require database_path('migrations/2026_08_30_000002_backfill_historical_academic_data.php');
        $m2->up();

        // 3. Assertions
        $this->assertDatabaseHas('subjects', ['id' => 99, 'name' => 'Materia Histórica 99']);
        $this->assertDatabaseHas('plannings', ['id' => $planning->id, 'title' => 'Planificación Histórica Pre-K005']);

        $placeholderArea = AcademicArea::where('code', 'PENDIENTE_AREA')->first();
        $this->assertNotNull($placeholderArea);
        $this->assertFalse((bool) $placeholderArea->is_active);

        $freshSubject = Subject::find(99);
        $this->assertEquals($placeholderArea->id, $freshSubject->academic_area_id);

        $freshPlanning = Planning::find($planning->id);
        $this->assertNotNull($freshPlanning->assignment_id);
        $this->assertFalse((bool) $freshPlanning->assignment->is_active);

        // Assert RESTRICT constraint active
        $this->expectException(\Illuminate\Database\QueryException::class);
        $freshSubject->delete();
    }

    public function test_complete_role_authorization_across_academic_cruds()
    {
        $routes = [
            'academic-areas.index',
            'courses.index',
            'parallels.index',
            'subjects.index',
            'teaching-assignments.index',
        ];

        // Docente receives 403 on all academic CRUDs
        $this->actingAs($this->docenteA);
        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertStatus(403);
        }

        // Unroled user receives 403
        $unroledUser = User::factory()->create(['is_active' => true]);
        $this->actingAs($unroledUser);
        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertStatus(403);
        }

        // Inactive user is blocked
        $inactiveSecretaria = User::factory()->create(['is_active' => false]);
        $inactiveSecretaria->assignRole('secretaria');
        $this->actingAs($inactiveSecretaria);
        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }

        // Secretaria can access all
        $this->actingAs($this->secretaria);
        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertStatus(200);
        }

        // Vicerrectorado can access all
        $vicerrector = User::factory()->create(['is_active' => true]);
        $vicerrector->assignRole('vicerrectorado');
        $this->actingAs($vicerrector);
        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertStatus(200);
        }
    }

    public function test_planning_integrity_server_side_derivation_and_date_validation()
    {
        $this->actingAs($this->docenteA);

        $assignmentA = TeachingAssignment::create([
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        $file = $this->createValidPdfFile('valid.pdf');

        // Try manipulating user_id and subject_id in payload
        $response = $this->post(route('plannings.store'), [
            'title' => 'Plan Tampered Payload',
            'assignment_id' => $assignmentA->id,
            'user_id' => $this->docenteB->id, // Attempting to set Docente B
            'subject_id' => 9999, // Attempting fake subject
            'week_start' => '2026-08-30',
            'week_end' => '2026-09-03',
            'file' => $file,
        ]);

        $response->assertRedirect(route('plannings.index'));

        // Assert DB record ignores tampered payload and derives user_id and subject_id from assignment
        $savedPlanning = Planning::where('title', 'Plan Tampered Payload')->first();
        $this->assertNotNull($savedPlanning);
        $this->assertEquals($this->docenteA->id, $savedPlanning->user_id);
        $this->assertEquals($this->subject->id, $savedPlanning->subject_id);

        // Invalid dates (week_end before week_start)
        $file2 = $this->createValidPdfFile('invalid_dates.pdf');
        $response2 = $this->post(route('plannings.store'), [
            'title' => 'Plan Dates Bad',
            'assignment_id' => $assignmentA->id,
            'week_start' => '2026-09-05',
            'week_end' => '2026-09-01',
            'file' => $file2,
        ]);
        $response2->assertSessionHasErrors(['week_end']);

        // Failed assignment upload leaves zero orphaned files
        Storage::fake('private_plannings');
        $file3 = $this->createValidPdfFile('failed.pdf');
        $this->post(route('plannings.store'), [
            'title' => 'Plan Bad Assignment',
            'assignment_id' => 99999,
            'week_start' => '2026-08-30',
            'week_end' => '2026-09-03',
            'file' => $file3,
        ]);

        $this->assertCount(0, Storage::disk('private_plannings')->allFiles());
    }

    public function test_catalog_deactivation_preserves_history_and_filters_new_dropdowns()
    {
        // 1. Create active assignment and planning
        $assignment = TeachingAssignment::create([
            'teacher_id' => $this->docenteA->id,
            'subject_id' => $this->subject->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        $file = $this->createValidPdfFile('hist_plan.pdf');
        $this->actingAs($this->docenteA)->post(route('plannings.store'), [
            'title' => 'Planificación con Catálogo que se Desactivará',
            'assignment_id' => $assignment->id,
            'week_start' => '2026-08-30',
            'week_end' => '2026-09-03',
            'file' => $file,
        ]);

        $planning = Planning::where('title', 'Planificación con Catálogo que se Desactivará')->first();

        // 2. Deactivate subject and assignment
        $this->subject->update(['is_active' => false]);
        $assignment->update(['is_active' => false]);

        // 3. Historical planning remains accessible
        $this->actingAs($this->docenteA)->get(route('plannings.view', $planning))->assertStatus(200);

        // 4. Inactive assignment excluded from new planning creation dropdown in plannings.index
        $response = $this->actingAs($this->docenteA)->get(route('plannings.index'));
        $response->assertStatus(200);
        $response->assertDontSee('<option value="'.$assignment->id.'">');

        // 5. Active assignment of Docente B not visible to Docente A
        $assignmentB = TeachingAssignment::create([
            'teacher_id' => $this->docenteB->id,
            'subject_id' => Subject::create(['name' => 'Materia Docente B', 'code' => 'MDB', 'is_active' => true])->id,
            'course_id' => $this->course->id,
            'parallel_id' => $this->parallel->id,
            'is_active' => true,
        ]);

        $responseDocenteA = $this->actingAs($this->docenteA)->get(route('plannings.index'));
        $responseDocenteA->assertDontSee('<option value="'.$assignmentB->id.'">');
    }

    protected function createValidPdfFile(string $filename = 'test.pdf'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('pdf_').'.pdf';
        file_put_contents($tempPath, "%PDF-1.4\n%EOF");

        return new UploadedFile($tempPath, $filename, 'application/pdf', null, true);
    }
}
