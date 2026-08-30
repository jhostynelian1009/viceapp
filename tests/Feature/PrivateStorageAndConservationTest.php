<?php

namespace Tests\Feature;

use App\Models\Planning;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateStorageAndConservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $docente;

    protected User $otherDocente;

    protected User $secretaria;

    protected User $vicerrectorado;

    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->docente = User::factory()->create(['is_active' => true]);
        $this->docente->assignRole('docente');

        $this->otherDocente = User::factory()->create(['is_active' => true]);
        $this->otherDocente->assignRole('docente');

        $this->secretaria = User::factory()->create(['is_active' => true]);
        $this->secretaria->assignRole('secretaria');

        $this->vicerrectorado = User::factory()->create(['is_active' => true]);
        $this->vicerrectorado->assignRole('vicerrectorado');

        $this->subject = Subject::create([
            'name' => 'Matemáticas',
            'code' => 'MAT-101',
        ]);
    }

    protected function createValidPdfFile(string $filename = 'test.pdf'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('pdf_').'.pdf';
        file_put_contents($tempPath, "%PDF-1.4\n%EOF");

        return new UploadedFile($tempPath, $filename, 'application/pdf', null, true);
    }

    protected function createInvalidPdfFile(string $filename = 'test.pdf'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('pdf_').'.pdf';
        file_put_contents($tempPath, 'NOT A PDF FILE');

        return new UploadedFile($tempPath, $filename, 'application/pdf', null, true);
    }

    protected function createValidDocFile(string $filename = 'test.doc'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('doc_').'.doc';
        file_put_contents($tempPath, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1SomeDocContentHere");

        return new UploadedFile($tempPath, $filename, 'application/msword', null, true);
    }

    protected function createInvalidDocFile(string $filename = 'test.doc'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('doc_').'.doc';
        file_put_contents($tempPath, 'NOT A DOC FILE');

        return new UploadedFile($tempPath, $filename, 'application/msword', null, true);
    }

    protected function createValidDocxFile(string $filename = 'test.docx'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('docx_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document></w:document>');
        $zip->close();

        return new UploadedFile($tempPath, $filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    protected function createDocxFileWithPartialStructure(string $filename, bool $contentTypes, bool $wordDoc): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('docx_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($contentTypes) {
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
        }
        if ($wordDoc) {
            $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document></w:document>');
        }
        $zip->close();

        return new UploadedFile($tempPath, $filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    protected function createZipFileWithMacros(string $filename = 'macros.docx'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('docx_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document></w:document>');
        $zip->addFromString('word/vbaProject.bin', 'macro binary');
        $zip->close();

        return new UploadedFile($tempPath, $filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    protected function createLargePdfFile(string $filename = 'big.pdf'): UploadedFile
    {
        $tempPath = sys_get_temp_dir().'/'.uniqid('pdf_').'.pdf';
        $handle = fopen($tempPath, 'wb');
        fwrite($handle, "%PDF-1.4\n");
        fseek($handle, 11 * 1024 * 1024);
        fwrite($handle, '%EOF');
        fclose($handle);

        return new UploadedFile($tempPath, $filename, 'application/pdf', null, true);
    }

    public function test_valid_pdf_stored_in_private_disk_with_random_filename(): void
    {
        Storage::fake('private_plannings');
        Storage::fake('public');

        $pdf = $this->createValidPdfFile('planificacion.pdf');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación Semanal PDF',
            'file' => $pdf,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertRedirect(route('plannings.index'));

        $planning = Planning::first();
        $this->assertNotNull($planning);
        $this->assertNotEquals('planificacion.pdf', $planning->file_path);
        $this->assertStringEndsWith('.pdf', $planning->file_path);

        Storage::disk('private_plannings')->assertExists($planning->file_path);
        Storage::disk('public')->assertMissing($planning->file_path);
    }

    public function test_invalid_pdf_signature_rejected(): void
    {
        Storage::fake('private_plannings');

        $pdf = $this->createInvalidPdfFile('planificacion.pdf');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación Invalida',
            'file' => $pdf,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertEquals(0, Planning::count());
    }

    public function test_valid_doc_stored_in_private_disk(): void
    {
        Storage::fake('private_plannings');

        $doc = $this->createValidDocFile('planificacion.doc');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación Semanal DOC',
            'file' => $doc,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertRedirect(route('plannings.index'));

        $planning = Planning::first();
        $this->assertNotNull($planning);
        Storage::disk('private_plannings')->assertExists($planning->file_path);
    }

    public function test_invalid_doc_signature_rejected(): void
    {
        Storage::fake('private_plannings');

        $doc = $this->createInvalidDocFile('planificacion.doc');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación Invalida DOC',
            'file' => $doc,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertEquals(0, Planning::count());
    }

    public function test_valid_docx_stored_in_private_disk(): void
    {
        Storage::fake('private_plannings');
        Storage::fake('public');

        $docx = $this->createValidDocxFile('planificacion.docx');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Planificación DOCX',
            'file' => $docx,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertRedirect(route('plannings.index'));

        $planning = Planning::first();
        $this->assertNotNull($planning);
        Storage::disk('private_plannings')->assertExists($planning->file_path);
    }

    public function test_invalid_docx_zip_structure_rejected(): void
    {
        Storage::fake('private_plannings');

        // Create fake zip without word/document.xml
        $tempPath = sys_get_temp_dir().'/'.uniqid('fake_').'.zip';
        $zip = new \ZipArchive;
        $zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('fake.txt', 'not a word document');
        $zip->close();

        $fakeDocx = new UploadedFile($tempPath, 'fake.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Fake DOCX',
            'file' => $fakeDocx,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertEquals(0, Planning::count());
    }

    public function test_docx_with_macros_rejected(): void
    {
        Storage::fake('private_plannings');

        $macroDocx = $this->createZipFileWithMacros('macros.docx');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Macro DOCX',
            'file' => $macroDocx,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertEquals(0, Planning::count());
    }

    public function test_file_exceeding_max_size_rejected(): void
    {
        Storage::fake('private_plannings');

        $largePdf = $this->createLargePdfFile('big.pdf'); // > 10MB

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Big File',
            'file' => $largePdf,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertEquals(0, Planning::count());
    }

    public function test_owner_teacher_and_vicerrectorado_can_download(): void
    {
        Storage::fake('private_plannings');

        $pdf = $this->createValidPdfFile('doc.pdf');
        $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Owner Doc',
            'file' => $pdf,
            'subject_id' => $this->subject->id,
        ]);

        $planning = Planning::first();

        // Owner teacher download
        $response1 = $this->actingAs($this->docente)->get(route('plannings.download', $planning));
        $response1->assertOk();

        // Vicerrectorado download
        $response2 = $this->actingAs($this->vicerrectorado)->get(route('plannings.download', $planning));
        $response2->assertOk();
    }

    public function test_secretaria_and_other_teacher_receive_403_on_download(): void
    {
        Storage::fake('private_plannings');

        $pdf = $this->createValidPdfFile('doc.pdf');
        $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Private Doc',
            'file' => $pdf,
            'subject_id' => $this->subject->id,
        ]);

        $planning = Planning::first();

        // Secretaría 403
        $this->actingAs($this->secretaria)->get(route('plannings.download', $planning))->assertForbidden();

        // Other docente 403
        $this->actingAs($this->otherDocente)->get(route('plannings.download', $planning))->assertForbidden();
    }

    public function test_visitor_cannot_download(): void
    {
        Storage::fake('private_plannings');

        $planning = Planning::create([
            'user_id' => $this->docente->id,
            'title' => 'Sample',
            'file_path' => 'sample.pdf',
            'subject_id' => $this->subject->id,
        ]);

        $this->get(route('plannings.download', $planning))->assertRedirect(route('login'));
    }

    public function test_pdf_preview_serves_inline_response(): void
    {
        Storage::fake('private_plannings');

        $pdf = $this->createValidPdfFile('doc.pdf');
        $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'PDF Preview',
            'file' => $pdf,
            'subject_id' => $this->subject->id,
        ]);

        $planning = Planning::first();

        $response = $this->actingAs($this->docente)->get(route('plannings.preview', $planning));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_docx_preview_degrades_to_download(): void
    {
        Storage::fake('private_plannings');

        $docx = $this->createValidDocxFile('doc.docx');
        $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Docx Preview',
            'file' => $docx,
            'subject_id' => $this->subject->id,
        ]);

        $planning = Planning::first();

        $response = $this->actingAs($this->docente)->get(route('plannings.preview', $planning));
        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=docx-preview.docx');
    }

    public function test_non_existent_file_returns_clean_404(): void
    {
        Storage::fake('private_plannings');

        $planning = Planning::create([
            'user_id' => $this->docente->id,
            'title' => 'Missing File Planning',
            'file_path' => 'non_existent_file.pdf',
            'subject_id' => $this->subject->id,
        ]);

        $response = $this->actingAs($this->docente)->get(route('plannings.download', $planning));
        $response->assertStatus(404);

        $responsePreview = $this->actingAs($this->docente)->get(route('plannings.preview', $planning));
        $responsePreview->assertStatus(404);
    }

    public function test_migration_command_dry_run_and_force(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        Storage::fake('private_plannings');

        // Create 1 associated file and 1 orphan file in public disk
        Storage::disk('public')->put('plannings/associated.pdf', "%PDF-1.4\n%EOF");
        Storage::disk('public')->put('plannings/orphan.pdf', "%PDF-1.4\n%EOF");

        $planning = Planning::create([
            'user_id' => $this->docente->id,
            'title' => 'Associated Planning',
            'file_path' => 'plannings/associated.pdf',
            'subject_id' => $this->subject->id,
        ]);

        // Dry-run command
        Artisan::call('migrate:private-documents');
        $output = Artisan::output();
        $this->assertStringContainsString('Modo DRY-RUN activo', $output);

        // Files still in public disk after dry run
        Storage::disk('public')->assertExists('plannings/associated.pdf');
        Storage::disk('public')->assertExists('plannings/orphan.pdf');

        // Execute migration command with --force
        Artisan::call('migrate:private-documents', ['--force' => true]);
        $outputForce = Artisan::output();
        $this->assertStringContainsString('Migración completada exitosamente', $outputForce);

        // Public files moved
        Storage::disk('public')->assertMissing('plannings/associated.pdf');
        Storage::disk('public')->assertMissing('plannings/orphan.pdf');
        Storage::disk('private_plannings')->assertExists('associated.pdf');
    }

    public function test_public_url_returns_404_and_no_document_content(): void
    {
        $response = $this->get('/storage/plannings/3C2L5SUzfcb3aFDdJ8lw4dAh3YG9lngHpiPSqIaI.pdf');
        $response->assertStatus(404);
    }

    public function test_migration_command_detects_collision_and_aborts(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        Storage::fake('private_plannings');

        // Same filename in public and private/quarantine with different content, no DB record (orphan)
        Storage::disk('public')->put('plannings/collision.pdf', "%PDF-1.4\n%EOF");
        Storage::disk('private')->put('quarantine/collision.pdf', 'private-different-content');

        $exitCode = Artisan::call('migrate:private-documents', ['--force' => true]);
        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('COLISIÓN EN CUARENTENA', $output);
    }

    public function test_foreign_key_restricts_deleting_user_or_subject_with_plannings(): void
    {
        Storage::fake('private_plannings');

        $planning = Planning::create([
            'user_id' => $this->docente->id,
            'title' => 'Restricted FK Planning',
            'file_path' => 'test.pdf',
            'subject_id' => $this->subject->id,
        ]);

        // Attempting to delete user or subject must throw QueryException due to restrict constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->docente->delete();
    }

    public function test_cleanup_after_persistence_failure(): void
    {
        Storage::fake('private_plannings');

        $sensitiveExceptionMessage = 'Database error in /var/www/html/viceapp/app/Models/Planning.php: Table "plannings" not found. Query: INSERT INTO plannings (title, file_path) VALUES ("Will Fail DB", "xyz")';

        // Throw an exception when attempting to save a Planning record
        \App\Models\Planning::creating(function ($model) use ($sensitiveExceptionMessage) {
            throw new \Exception($sensitiveExceptionMessage);
        });

        $pdf = $this->createValidPdfFile('planificacion.pdf');

        $response = $this->actingAs($this->docente)->post(route('plannings.store'), [
            'title' => 'Will Fail DB',
            'file' => $pdf,
            'subject_id' => $this->subject->id,
        ]);

        $response->assertSessionHas('error');

        $sessionError = session('error');
        $this->assertEquals(
            'No se pudo procesar la planificación. Inténtelo de nuevo o contacte con el administrador.',
            $sessionError
        );

        // Ensure no internal details are leaked to the user/session
        $this->assertStringNotContainsString('/var/www/html/viceapp', $sessionError);
        $this->assertStringNotContainsString('plannings', $sessionError);
        $this->assertStringNotContainsString('INSERT INTO', $sessionError);
        $this->assertStringNotContainsString('Database error', $sessionError);

        $this->assertEquals(0, Planning::count());

        // Assert that private disk is empty, indicating compensatory cleanup was executed
        $this->assertEmpty(Storage::disk('private_plannings')->allFiles());
    }
}
