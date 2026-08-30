<?php

namespace Tests\Feature;

use App\Models\Planning;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationAndAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected User $docenteA;

    protected User $docenteB;

    protected User $secretaria;

    protected User $vicerrector;

    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $roleDocente = Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        $roleSecretaria = Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        $roleVicerrector = Role::firstOrCreate(['name' => 'vicerrectorado', 'guard_name' => 'web']);

        $this->docenteA = User::factory()->create([
            'email' => 'docenteA@example.com',
            'is_active' => true,
        ]);
        $this->docenteA->assignRole($roleDocente);

        $this->docenteB = User::factory()->create([
            'email' => 'docenteB@example.com',
            'is_active' => true,
        ]);
        $this->docenteB->assignRole($roleDocente);

        $this->secretaria = User::factory()->create([
            'email' => 'secretaria@example.com',
            'is_active' => true,
        ]);
        $this->secretaria->assignRole($roleSecretaria);

        $this->vicerrector = User::factory()->create([
            'email' => 'vicerrector@example.com',
            'is_active' => true,
        ]);
        $this->vicerrector->assignRole($roleVicerrector);

        $this->subject = Subject::firstOrCreate(['name' => 'Matemáticas']);
    }

    // --- REGISTRO Y CUENTAS ---

    public function test_public_registration_get_is_disabled(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    public function test_public_registration_post_is_disabled(): void
    {
        $response = $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(404);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_active_user_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'docenteA@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->docenteA);
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $this->docenteA->is_active = false;
        $this->docenteA->save();

        $response = $this->post('/login', [
            'email' => 'docenteA@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_session_is_terminated_by_middleware(): void
    {
        $this->actingAs($this->docenteA);

        $this->docenteA->is_active = false;
        $this->docenteA->save();

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_docente_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->docenteA)->get('/teachers');
        $response->assertStatus(403);
    }

    public function test_secretaria_and_vicerrectorado_can_manage_teachers(): void
    {
        $responseSec = $this->actingAs($this->secretaria)->get('/teachers');
        $responseSec->assertStatus(200);

        $responseVic = $this->actingAs($this->vicerrector)->get('/teachers');
        $responseVic->assertStatus(200);
    }

    public function test_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->secretaria)
            ->patch(route('teachers.toggleActive', $this->secretaria));

        $response->assertStatus(403);
        $this->assertTrue($this->secretaria->fresh()->is_active);
    }

    public function test_cannot_deactivate_last_active_critical_user(): void
    {
        $response = $this->actingAs($this->secretaria)
            ->patch(route('teachers.toggleActive', $this->vicerrector));

        $response->assertStatus(403);
        $this->assertTrue($this->vicerrector->fresh()->is_active);
    }

    public function test_deactivating_user_preserves_historical_records(): void
    {
        $planning = Planning::create([
            'user_id' => $this->docenteA->id,
            'title' => 'Planificación Historia',
            'file_path' => 'plannings/demo.pdf',
            'subject_id' => $this->subject->id,
            'status' => 'borrador',
        ]);

        $this->actingAs($this->secretaria)
            ->patch(route('teachers.toggleActive', $this->docenteA));

        $this->assertFalse($this->docenteA->fresh()->is_active);
        $this->assertDatabaseHas('plannings', ['id' => $planning->id]);
    }

    // --- PLANIFICACIONES E IDOR ---

    public function test_docente_can_manage_own_planning(): void
    {
        $planning = Planning::create([
            'user_id' => $this->docenteA->id,
            'title' => 'Mi Planificación',
            'file_path' => 'plannings/mine.pdf',
            'subject_id' => $this->subject->id,
            'status' => 'borrador',
        ]);

        $response = $this->actingAs($this->docenteA)->get(route('plannings.view', $planning));
        $response->assertStatus(200);

        $submitRes = $this->actingAs($this->docenteA)->patch(route('plannings.updateStatus', $planning), [
            'status' => 'revisión',
        ]);
        $submitRes->assertRedirect(route('plannings.index'));
        $this->assertEquals('revisión', $planning->fresh()->status);
    }

    public function test_docente_cannot_access_download_comment_or_submit_other_docente_planning(): void
    {
        $planningB = Planning::create([
            'user_id' => $this->docenteB->id,
            'title' => 'Plan de B',
            'file_path' => 'plannings/b.pdf',
            'subject_id' => $this->subject->id,
            'status' => 'borrador',
        ]);

        $this->actingAs($this->docenteA)->get(route('plannings.view', $planningB))->assertStatus(403);
        $this->actingAs($this->docenteA)->get(route('plannings.download', $planningB))->assertStatus(403);
        $this->actingAs($this->docenteA)->post(route('comments.store', $planningB), [
            'body' => 'Intento de comentario',
        ])->assertStatus(403);

        $this->actingAs($this->docenteA)->patch(route('plannings.updateStatus', $planningB), [
            'status' => 'revisión',
        ])->assertStatus(403);

        $this->actingAs($this->docenteA)->delete(route('plannings.destroy', $planningB))->assertStatus(403);
        $this->assertEquals('borrador', $planningB->fresh()->status);
    }

    public function test_secretaria_can_view_index_metadata_but_cannot_access_detail_view_download_comment_approve_or_reject(): void
    {
        $planning = Planning::create([
            'user_id' => $this->docenteA->id,
            'title' => 'Plan en Revisión',
            'file_path' => 'plannings/rev.pdf',
            'subject_id' => $this->subject->id,
            'status' => 'revisión',
        ]);

        // Listado de metadatos -> OK (200)
        $this->actingAs($this->secretaria)->get(route('plannings.index'))->assertStatus(200);

        // Detalle académico / Vista previa -> 403
        $this->actingAs($this->secretaria)->get(route('plannings.view', $planning))->assertStatus(403);

        // Aprobar -> 403
        $this->actingAs($this->secretaria)->patch(route('plannings.updateStatus', $planning), [
            'status' => 'aprobado',
        ])->assertStatus(403);

        // Rechazar -> 403
        $this->actingAs($this->secretaria)->patch(route('plannings.updateStatus', $planning), [
            'status' => 'rechazado',
        ])->assertStatus(403);

        // Comentar -> 403
        $this->actingAs($this->secretaria)->post(route('comments.store', $planning), [
            'body' => 'Intento de comentario por Secretaría',
        ])->assertStatus(403);

        // Descargar -> 403
        $this->actingAs($this->secretaria)->get(route('plannings.download', $planning))->assertStatus(403);

        $this->assertEquals('revisión', $planning->fresh()->status);
    }

    public function test_vicerrectorado_can_review_approve_reject_and_comment(): void
    {
        $planning = Planning::create([
            'user_id' => $this->docenteA->id,
            'title' => 'Plan a Aprobar',
            'file_path' => 'plannings/app.pdf',
            'subject_id' => $this->subject->id,
            'status' => 'revisión',
        ]);

        $this->actingAs($this->vicerrector)->get(route('plannings.review'))->assertStatus(200);

        $this->actingAs($this->vicerrector)->post(route('comments.store', $planning), [
            'body' => 'Excelente contenido académico.',
        ])->assertRedirect();
        $this->assertDatabaseHas('comments', ['planning_id' => $planning->id, 'user_id' => $this->vicerrector->id]);

        $this->actingAs($this->vicerrector)->patch(route('plannings.updateStatus', $planning), [
            'status' => 'aprobado',
        ])->assertRedirect(route('plannings.review'));

        $this->assertEquals('aprobado', $planning->fresh()->status);
    }

    // --- REPORTES ---

    public function test_docente_and_unroled_user_received_403_on_reports(): void
    {
        $this->actingAs($this->docenteA)->get('/reports')->assertStatus(403);
        $this->actingAs($this->docenteA)->get('/reports/download/pdf')->assertStatus(403);

        $unroledUser = User::factory()->create(['is_active' => true]);
        $this->actingAs($unroledUser)->get('/reports')->assertStatus(403);
        $this->actingAs($unroledUser)->get('/reports/download/pdf')->assertStatus(403);
    }

    public function test_secretaria_and_vicerrectorado_can_access_reports(): void
    {
        $this->actingAs($this->secretaria)->get('/reports')->assertStatus(200);
        $this->actingAs($this->vicerrector)->get('/reports')->assertStatus(200);
    }

    // --- NOTIFICACIONES ---

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $notification = DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\CommentNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->docenteA->id,
            'data' => ['message' => 'Test Notification'],
            'read_at' => null,
        ]);

        $this->actingAs($this->docenteA)->post(route('notifications.read', $notification))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_user_notification_as_read(): void
    {
        $notificationB = DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\CommentNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->docenteB->id,
            'data' => ['message' => 'Notification for B'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->docenteA)->post(route('notifications.read', $notificationB));

        $response->assertStatus(403);
        $this->assertNull($notificationB->fresh()->read_at);
    }

    public function test_nonexistent_notification_returns_404(): void
    {
        $fakeUuid = (string) Str::uuid();
        $response = $this->actingAs($this->docenteA)->post('/notifications/'.$fakeUuid.'/read');
        $response->assertStatus(404);
    }
}
