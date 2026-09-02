<?php

namespace Tests\Feature;

use App\Enums\PlanningStatus;
use App\Models\AcademicArea;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Parallel;
use App\Models\Planning;
use App\Models\PlanningVersion;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\PlanningWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $docente;

    protected User $docenteOtro;

    protected User $secretaria;

    protected User $vicerrectorado;

    protected TeachingAssignment $assignment;

    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'docente']);
        Role::firstOrCreate(['name' => 'secretaria']);
        Role::firstOrCreate(['name' => 'vicerrectorado']);

        $this->docente = User::factory()->create(['is_active' => true]);
        $this->docente->assignRole('docente');

        $this->docenteOtro = User::factory()->create(['is_active' => true]);
        $this->docenteOtro->assignRole('docente');

        $this->secretaria = User::factory()->create(['is_active' => true]);
        $this->secretaria->assignRole('secretaria');

        $this->vicerrectorado = User::factory()->create(['is_active' => true]);
        $this->vicerrectorado->assignRole('vicerrectorado');

        $area = AcademicArea::create(['name' => 'Ciencias', 'code' => 'CC', 'is_active' => true]);
        $this->subject = Subject::create(['academic_area_id' => $area->id, 'name' => 'Matemáticas', 'code' => 'MAT-01', 'is_active' => true]);
        $course = Course::create(['name' => 'Primero', 'is_active' => true]);
        $parallel = Parallel::create(['name' => 'A', 'is_active' => true]);

        $this->assignment = TeachingAssignment::create([
            'teacher_id' => $this->docente->id,
            'subject_id' => $this->subject->id,
            'course_id' => $course->id,
            'parallel_id' => $parallel->id,
            'is_active' => true,
        ]);
    }

    protected function createValidPdfFile(string $name = 'planificacion.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");
    }

    public function test_creation_generates_draft_and_version_1(): void
    {
        Storage::fake('private_plannings');

        $pdf = $this->createValidPdfFile('planificacion.pdf');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación Semanal 1',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
            'file' => $pdf,
        ]);

        $response->assertRedirect(route('plannings.index'));

        $planning = Planning::first();
        $this->assertNotNull($planning);
        $this->assertEquals(PlanningStatus::DRAFT, $planning->status);
        $this->assertEquals(1, $planning->versions()->count());

        $version = $planning->currentVersion;
        $this->assertEquals(1, $version->version);
        $this->assertEquals($this->docente->id, $version->uploaded_by);
        $this->assertEquals(64, strlen($version->checksum));

        // Confirm audit log created
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->docente->id,
            'event' => 'planning.draft_created',
            'auditable_id' => $planning->id,
        ]);
    }

    public function test_draft_is_not_counted_as_pending(): void
    {
        Storage::fake('private_plannings');

        $pdf = $this->createValidPdfFile();

        $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación Borrador',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
            'file' => $pdf,
        ]);

        $this->actingAs($this->vicerrectorado)
            ->get(route('plannings.review'))
            ->assertDontSee('Planificación Borrador');
    }

    public function test_owner_teacher_can_submit_valid_draft(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Planificación Para Enviar',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);

        $response = $this->actingAs($this->docente)->post(route('plannings.submit', $planning));
        $response->assertRedirect(route('plannings.index'));

        $this->assertEquals(PlanningStatus::PENDING, $planning->fresh()->status);
    }

    public function test_other_teacher_cannot_submit_planning(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Planificación Privada',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);

        $this->actingAs($this->docenteOtro)
            ->post(route('plannings.submit', $planning))
            ->assertStatus(403);
    }

    public function test_secretaria_cannot_submit_approve_or_reject(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Planificación Test',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);

        $this->actingAs($this->secretaria)->post(route('plannings.submit', $planning))->assertStatus(403);
        $this->actingAs($this->secretaria)->post(route('plannings.approve', $planning))->assertStatus(403);
        $this->actingAs($this->secretaria)->post(route('plannings.reject', $planning), ['comment' => 'Motivo'])->assertStatus(403);
    }

    public function test_vicerrectorado_can_approve_pending(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Planificación Aprobable',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);

        $response = $this->actingAs($this->vicerrectorado)->post(route('plannings.approve', $planning));
        $response->assertRedirect(route('plannings.review'));

        $this->assertEquals(PlanningStatus::APPROVED, $planning->fresh()->status);
        $this->assertDatabaseHas('planning_reviews', [
            'planning_id' => $planning->id,
            'reviewer_id' => $this->vicerrectorado->id,
            'decision' => 'approved',
        ]);
    }

    public function test_vicerrectorado_can_reject_pending_with_reason(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Planificación Rechazable',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);

        $response = $this->actingAs($this->vicerrectorado)->post(route('plannings.reject', $planning), [
            'comment' => 'Falta detallar la metodología de evaluación.',
        ]);
        $response->assertRedirect(route('plannings.review'));

        $this->assertEquals(PlanningStatus::REJECTED, $planning->fresh()->status);
        $this->assertDatabaseHas('planning_reviews', [
            'planning_id' => $planning->id,
            'reviewer_id' => $this->vicerrectorado->id,
            'decision' => 'rejected',
            'comment' => 'Falta detallar la metodología de evaluación.',
        ]);
    }

    public function test_rejection_without_reason_fails_without_changes(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Planificación Requerida',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);

        $response = $this->actingAs($this->vicerrectorado)->post(route('plannings.reject', $planning), [
            'comment' => '   ',
        ]);
        $response->assertSessionHasErrors(['comment']);

        $this->assertEquals(PlanningStatus::PENDING, $planning->fresh()->status);
    }

    public function test_rejected_planning_cannot_be_resubmitted_without_new_version(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile('plan.pdf');
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Reacondicionado',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);
        $service->reject($planning, $this->vicerrectorado, 'Corregir apartado 2.');

        // Attempt resubmit without new version
        $response = $this->actingAs($this->docente)->post(route('plannings.submit', $planning));
        $response->assertSessionHasErrors(['file']);
        $this->assertEquals(PlanningStatus::REJECTED, $planning->fresh()->status);
    }

    public function test_resubmission_creates_new_version_and_preserves_previous(): void
    {
        Storage::fake('private_plannings');
        $pdf1 = $this->createValidPdfFile('plan_v1.pdf');
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Versionado',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf1);
        $service->submit($planning, $this->docente);
        $service->reject($planning, $this->vicerrectorado, 'Añadir objetivos.');

        // Upload version 2
        $pdf2 = $this->createValidPdfFile('plan_v2.pdf');
        $service->updateDraft($planning, $this->docente, ['title' => 'Plan Versionado V2'], $pdf2);

        $this->assertEquals(2, $planning->versions()->count());

        // Now submit version 2
        $this->actingAs($this->docente)->post(route('plannings.submit', $planning));
        $this->assertEquals(PlanningStatus::PENDING, $planning->fresh()->status);
    }

    public function test_approved_planning_cannot_be_edited_or_replaced(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile('plan.pdf');
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Inmutable',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);
        $service->approve($planning, $this->vicerrectorado);

        $pdf2 = $this->createValidPdfFile('plan_fake.pdf');
        $this->actingAs($this->docente)->put(route('plannings.update', $planning), [
            'title' => 'Título Modificado',
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
            'file' => $pdf2,
        ])->assertStatus(403);
    }

    public function test_audit_log_is_append_only(): void
    {
        $log = AuditLog::create([
            'actor_id' => $this->docente->id,
            'event' => 'test.event',
            'auditable_type' => User::class,
            'auditable_id' => $this->docente->id,
            'created_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $log->update(['event' => 'tampered.event']);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'actor_id' => $this->docente->id,
            'event' => 'test.event',
            'auditable_type' => User::class,
            'auditable_id' => $this->docente->id,
            'created_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $log->delete();
    }

    public function test_unique_version_per_planning_constraint(): void
    {
        Storage::fake('private_plannings');
        $pdf = UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf');
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Duplicate Check',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);

        $this->expectException(\Illuminate\Database\QueryException::class);
        PlanningVersion::create([
            'planning_id' => $planning->id,
            'version' => 1, // Duplicate version number
            'file_path' => 'test.pdf',
            'file_disk' => 'private_plannings',
            'original_name' => 'test.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
            'checksum' => hash('sha256', 'test'),
            'uploaded_by' => $this->docente->id,
            'created_at' => now(),
        ]);
    }
}
