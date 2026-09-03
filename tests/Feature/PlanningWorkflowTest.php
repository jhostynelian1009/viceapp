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

    public function test_current_version_invariant_prevents_cross_assignment(): void
    {
        Storage::fake('private_plannings');
        $pdf1 = $this->createValidPdfFile('plan1.pdf');
        $pdf2 = $this->createValidPdfFile('plan2.pdf');
        $service = app(PlanningWorkflowService::class);

        $p1 = $service->createDraft($this->docente, [
            'title' => 'Plan 1',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf1);

        $p2 = $service->createDraft($this->docente, [
            'title' => 'Plan 2',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf2);

        $this->expectException(\InvalidArgumentException::class);
        $p1->current_version_id = $p2->current_version_id;
        $p1->save();
    }

    public function test_downloading_version_from_another_planning_returns_403(): void
    {
        Storage::fake('private_plannings');
        $pdf1 = $this->createValidPdfFile('plan1.pdf');
        $pdf2 = $this->createValidPdfFile('plan2.pdf');
        $service = app(PlanningWorkflowService::class);

        $p1 = $service->createDraft($this->docente, [
            'title' => 'Plan 1',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf1);

        $p2 = $service->createDraft($this->docente, [
            'title' => 'Plan 2',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf2);

        // Attempting to download P2's version using P1's URL route
        $this->actingAs($this->docente)
            ->get(route('plannings.versions.download', ['planning' => $p1->id, 'version' => $p2->current_version_id]))
            ->assertStatus(403);
    }

    public function test_concurrency_guard_prevents_double_approval(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Double Approval',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);

        $service->approve($planning, $this->vicerrectorado);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->approve($planning, $this->vicerrectorado);
    }

    public function test_concurrency_guard_prevents_approval_after_rejection(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Approval After Rejection',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);

        $service->reject($planning, $this->vicerrectorado, 'Motivo de rechazo.');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->approve($planning, $this->vicerrectorado);
    }

    public function test_concurrency_guard_prevents_double_submission(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Double Submit',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);
        $service->submit($planning, $this->docente);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->submit($planning, $this->docente);
    }

    public function test_secretaria_cannot_view_download_preview_or_comment(): void
    {
        Storage::fake('private_plannings');
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Private Access',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);

        $this->actingAs($this->secretaria)->get(route('plannings.view', $planning))->assertStatus(403);
        $this->actingAs($this->secretaria)->get(route('plannings.download', $planning))->assertStatus(403);
        $this->actingAs($this->secretaria)->get(route('plannings.preview', $planning))->assertStatus(403);
        $this->actingAs($this->secretaria)->post(route('comments.store', $planning), ['body' => 'Hola'])->assertStatus(403);
    }

    public function test_vicerrectorado_can_access_review_inbox_while_others_are_denied(): void
    {
        $this->actingAs($this->vicerrectorado)->get(route('plannings.review'))->assertStatus(200);
        $this->actingAs($this->secretaria)->get(route('plannings.review'))->assertStatus(403);
        $this->actingAs($this->docente)->get(route('plannings.review'))->assertStatus(403);
    }

    public function test_audit_logger_sanitizes_sensitive_keys(): void
    {
        $log = \App\Services\AuditLogger::log($this->docente, 'test.sanitization', $this->docente, [
            'password' => 'secret123',
            'token' => 'xyz789',
            'authorization' => 'Bearer token123',
            'content' => 'Document body text',
            'file' => 'binary_blob',
            'full_path' => 'C:\\xampp\\htdocs\\viceapp\\storage\\private\\doc.pdf',
            'sql' => 'SELECT * FROM users WHERE password = 1',
            'exception' => 'Exception: Internal error Stack trace: line 10',
            'title' => 'Título Normal',
        ], [
            'secret' => 'topsecret',
            'title' => 'Título Nuevo',
        ]);

        $this->assertArrayNotHasKey('password', $log->old_values);
        $this->assertArrayNotHasKey('token', $log->old_values);
        $this->assertArrayNotHasKey('authorization', $log->old_values);
        $this->assertArrayNotHasKey('content', $log->old_values);
        $this->assertArrayNotHasKey('file', $log->old_values);
        $this->assertEquals('doc.pdf', $log->old_values['full_path']);
        $this->assertEquals('[SQL Query Redacted]', $log->old_values['sql']);
        $this->assertEquals('[Exception Redacted]', $log->old_values['exception']);
        $this->assertEquals('Título Normal', $log->old_values['title']);

        $this->assertArrayNotHasKey('secret', $log->new_values);
        $this->assertEquals('Título Nuevo', $log->new_values['title']);
    }

    public function test_repair_legacy_metadata_migration_chain_and_safeguards(): void
    {
        Storage::fake('private_plannings');

        // 1. Setup legacy existing file
        $content = '%PDF-1.4 sample content for real hash';
        Storage::disk('private_plannings')->put('legacy_real.pdf', $content);
        $realChecksum = hash('sha256', $content);
        $realSize = strlen($content);

        $planningReal = Planning::create([
            'user_id' => $this->docente->id,
            'subject_id' => $this->subject->id,
            'assignment_id' => $this->assignment->id,
            'title' => 'Plan Legado Real',
            'file_path' => 'legacy_real.pdf',
            'status' => PlanningStatus::DRAFT,
            'week_start' => '2026-08-01',
            'week_end' => '2026-08-05',
        ]);

        $vReal = PlanningVersion::create([
            'planning_id' => $planningReal->id,
            'version' => 1,
            'file_path' => 'legacy_real.pdf',
            'file_disk' => 'private_plannings',
            'original_name' => 'planificacion.pdf',
            'mime' => 'application/octet-stream',
            'size' => 0,
            'checksum' => '0000000000000000000000000000000000000000000000000000000000000000',
            'uploaded_by' => $this->docente->id,
            'created_at' => now(),
        ]);
        $planningReal->update(['current_version_id' => $vReal->id]);

        // 2. Setup legacy missing file
        $planningMissing = Planning::create([
            'user_id' => $this->docente->id,
            'subject_id' => $this->subject->id,
            'assignment_id' => $this->assignment->id,
            'title' => 'Plan Legado Ausente',
            'file_path' => 'legacy_missing.pdf',
            'status' => PlanningStatus::DRAFT,
            'week_start' => '2026-08-01',
            'week_end' => '2026-08-05',
        ]);

        $vMissing = PlanningVersion::create([
            'planning_id' => $planningMissing->id,
            'version' => 1,
            'file_path' => 'legacy_missing.pdf',
            'file_disk' => 'private_plannings',
            'original_name' => 'missing_historical_file.pdf',
            'mime' => 'application/octet-stream',
            'size' => 0,
            'checksum' => '0000000000000000000000000000000000000000000000000000000000000000',
            'uploaded_by' => $this->docente->id,
            'created_at' => now(),
        ]);
        $planningMissing->update(['current_version_id' => $vMissing->id]);

        // Run migration 000010 repair
        $migration = require database_path('migrations/2026_09_03_000010_repair_legacy_planning_version_metadata.php');
        $migration->up();

        // Verify Case A: Existing file repaired honestly
        $vRealRefresh = $vReal->fresh();
        $this->assertEquals('verified', $vRealRefresh->integrity_status);
        $this->assertEquals($realChecksum, $vRealRefresh->checksum);
        $this->assertEquals($realSize, $vRealRefresh->size);
        $this->assertNull($vRealRefresh->original_name); // Synthetic name removed since no reliable historical source
        $this->assertNotNull($vRealRefresh->integrity_verified_at);

        // Verify Case B: Missing file handled honestly without fake zeros
        $vMissingRefresh = $vMissing->fresh();
        $this->assertEquals('missing_file', $vMissingRefresh->integrity_status);
        $this->assertNull($vMissingRefresh->checksum);
        $this->assertNull($vMissingRefresh->size);
        $this->assertNull($vMissingRefresh->mime);
        $this->assertNull($vMissingRefresh->original_name);
        $this->assertNull($vMissingRefresh->integrity_verified_at);

        // Download & submit & approval safeguards for missing file
        $this->actingAs($this->docente)->get(route('plannings.download', $planningMissing))->assertStatus(404);
        $this->actingAs($this->docente)->get(route('plannings.preview', $planningMissing))->assertStatus(404);

        $service = app(PlanningWorkflowService::class);
        try {
            $service->submit($planningMissing, $this->docente);
            $this->fail('Expected submit to fail for missing file');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $this->assertArrayHasKey('file', $ve->errors());
        }

        // Test running migration 000010 again is idempotent
        $migration->up();
        $this->assertEquals('verified', $vReal->fresh()->integrity_status);
        $this->assertEquals('missing_file', $vMissing->fresh()->integrity_status);
    }

    public function test_new_version_creation_audits_version_created_and_resubmitted(): void
    {
        Storage::fake('private_plannings');
        $pdf1 = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);

        $planning = $service->createDraft($this->docente, [
            'title' => 'Plan Audit Version Test',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf1);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'planning.version_created',
            'auditable_id' => $planning->id,
        ]);

        $service->submit($planning, $this->docente);
        $service->reject($planning, $this->vicerrectorado, 'Necesita corregir formato');

        $pdf2 = $this->createValidPdfFile();
        $service->updateDraft($planning, $this->docente, ['title' => 'Plan Audit Version Test v2'], $pdf2);
        $service->submit($planning, $this->docente);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'planning.resubmitted',
            'auditable_id' => $planning->id,
        ]);
    }

    public function test_audit_logger_recursive_sanitizes_nested_arrays(): void
    {
        $log = \App\Services\AuditLogger::log($this->docente, 'test.recursive_sanitization', $this->docente, [
            'user' => [
                'password' => 'secretPass',
                'token' => 'bearerToken',
                'authorization' => 'Basic authHeader',
                'secret' => 'topSecretKey',
                'nested' => [
                    'query' => 'SELECT * FROM users',
                    'file' => 'blob',
                    'path' => 'C:\\xampp\\htdocs\\viceapp\\config.php',
                    'normal' => 'Safe String',
                ],
            ],
        ]);

        $sanitized = $log->old_values;
        $this->assertArrayNotHasKey('password', $sanitized['user']);
        $this->assertArrayNotHasKey('token', $sanitized['user']);
        $this->assertArrayNotHasKey('authorization', $sanitized['user']);
        $this->assertArrayNotHasKey('secret', $sanitized['user']);
        $this->assertArrayNotHasKey('file', $sanitized['user']['nested']);
        $this->assertEquals('[SQL Query Redacted]', $sanitized['user']['nested']['query']);
        $this->assertEquals('config.php', $sanitized['user']['nested']['path']);
        $this->assertEquals('Safe String', $sanitized['user']['nested']['normal']);
    }

    public function test_secure_planning_version_integrity_default_and_000011_reconciliation(): void
    {
        Storage::fake('private_plannings');

        // Run migration 000011
        $migration11 = require database_path('migrations/2026_09_03_000011_secure_planning_version_integrity_default.php');
        $migration11->up();

        // 1. Inserting a version without integrity_status produces unknown_legacy_metadata (DB default), NEVER verified
        $pObj = Planning::create([
            'user_id' => $this->docente->id,
            'subject_id' => $this->subject->id,
            'assignment_id' => $this->assignment->id,
            'title' => 'Plan Default Integrity Test',
            'file_path' => 'default_test.pdf',
            'status' => PlanningStatus::DRAFT,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ]);

        $vRawId = \Illuminate\Support\Facades\DB::table('planning_versions')->insertGetId([
            'planning_id' => $pObj->id,
            'version' => 1,
            'file_path' => 'default_test.pdf',
            'file_disk' => 'private_plannings',
            'uploaded_by' => $this->docente->id,
            'created_at' => now(),
        ]);

        $vRaw = PlanningVersion::find($vRawId);
        $this->assertEquals('unknown_legacy_metadata', $vRaw->integrity_status);
        $this->assertNotEquals('verified', $vRaw->integrity_status);

        // 2. Setup an inconsistent record marked 'verified' manually with 64 zeros hash (physical file exists but metadata synthetic)
        Storage::disk('private_plannings')->put('zero_hash.pdf', 'dummy physical file content');
        $vZeroId = \Illuminate\Support\Facades\DB::table('planning_versions')->insertGetId([
            'planning_id' => $pObj->id,
            'version' => 2,
            'file_path' => 'zero_hash.pdf',
            'file_disk' => 'private_plannings',
            'checksum' => '0000000000000000000000000000000000000000000000000000000000000000',
            'size' => 0,
            'mime' => 'application/octet-stream',
            'integrity_status' => 'verified',
            'uploaded_by' => $this->docente->id,
            'created_at' => now(),
        ]);

        // 3. Setup a missing file record marked 'verified'
        $vMissingId = \Illuminate\Support\Facades\DB::table('planning_versions')->insertGetId([
            'planning_id' => $pObj->id,
            'version' => 3,
            'file_path' => 'missing_file.pdf',
            'file_disk' => 'private_plannings',
            'checksum' => 'a1b2c3d4e5f60718293a4b5c6d7e8f901234567890abcdef1234567890abcdef',
            'size' => 1024,
            'mime' => 'application/pdf',
            'integrity_status' => 'verified',
            'uploaded_by' => $this->docente->id,
            'created_at' => now(),
        ]);

        // Run 000011 reconciliation
        $migration11->up();

        // 64 zeros hash must NOT remain verified -> unknown_legacy_metadata
        $this->assertEquals('unknown_legacy_metadata', PlanningVersion::find($vZeroId)->integrity_status);
        $this->assertNull(PlanningVersion::find($vZeroId)->checksum);

        // Missing file must NOT remain verified -> missing_file
        $this->assertEquals('missing_file', PlanningVersion::find($vMissingId)->integrity_status);
        $this->assertNull(PlanningVersion::find($vMissingId)->checksum);

        // 4. Normal upload via service receives verified explicitly
        $pdf = $this->createValidPdfFile();
        $service = app(PlanningWorkflowService::class);
        $newPlan = $service->createDraft($this->docente, [
            'title' => 'Plan Verified Upload',
            'assignment_id' => $this->assignment->id,
            'week_start' => '2026-09-01',
            'week_end' => '2026-09-05',
        ], $pdf);

        $vNew = $newPlan->currentVersion;
        $this->assertEquals('verified', $vNew->integrity_status);
        $this->assertNotNull($vNew->integrity_verified_at);
        $this->assertEquals(64, strlen($vNew->checksum));
        $this->assertGreaterThan(0, $vNew->size);
        $this->assertNotEmpty($vNew->mime);

        // 5. Re-running 000011 is idempotent
        $migration11->up();
        $this->assertEquals('verified', $vNew->fresh()->integrity_status);
        $this->assertEquals('missing_file', PlanningVersion::find($vMissingId)->integrity_status);
        $this->assertEquals('unknown_legacy_metadata', PlanningVersion::find($vZeroId)->integrity_status);

        // 6. Confirm no versions or plannings were deleted
        $this->assertDatabaseHas('plannings', ['id' => $pObj->id]);
        $this->assertDatabaseHas('planning_versions', ['id' => $vRawId]);
        $this->assertDatabaseHas('planning_versions', ['id' => $vZeroId]);
        $this->assertDatabaseHas('planning_versions', ['id' => $vMissingId]);
    }
}
