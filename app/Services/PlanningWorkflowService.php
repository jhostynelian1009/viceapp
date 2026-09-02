<?php

namespace App\Services;

use App\Enums\PlanningStatus;
use App\Models\Planning;
use App\Models\PlanningReview;
use App\Models\PlanningVersion;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PlanningWorkflowService
{
    /**
     * Create a new planning in draft status with version 1.
     */
    public function createDraft(User $teacher, array $data, UploadedFile $file): Planning
    {
        $assignment = TeachingAssignment::where('id', $data['assignment_id'] ?? null)
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->first();

        if (! $assignment) {
            throw ValidationException::withMessages([
                'assignment_id' => 'La asignación docente seleccionada no es válida o no está activa.',
            ]);
        }

        $disk = Storage::disk('private_plannings');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $randomFileName = \Illuminate\Support\Str::random(40).'.'.$extension;
        $storedPath = $disk->putFileAs('', $file, $randomFileName);
        $fullPath = $disk->path($storedPath);

        try {
            return DB::transaction(function () use ($teacher, $data, $assignment, $file, $storedPath, $fullPath) {
                $checksum = hash_file('sha256', $fullPath);
                $size = filesize($fullPath);
                $mime = $file->getClientMimeType() ?: 'application/pdf';
                $originalName = $file->getClientOriginalName();

                $planning = Planning::create([
                    'user_id' => $teacher->id,
                    'assignment_id' => $assignment->id,
                    'subject_id' => $assignment->subject_id,
                    'title' => $data['title'],
                    'file_path' => $storedPath,
                    'status' => PlanningStatus::DRAFT,
                    'week_start' => $data['week_start'],
                    'week_end' => $data['week_end'],
                ]);

                $version = PlanningVersion::create([
                    'planning_id' => $planning->id,
                    'version' => 1,
                    'file_path' => $storedPath,
                    'file_disk' => 'private_plannings',
                    'original_name' => $originalName,
                    'mime' => $mime,
                    'size' => $size,
                    'checksum' => $checksum,
                    'uploaded_by' => $teacher->id,
                    'created_at' => now(),
                ]);

                $planning->update(['current_version_id' => $version->id]);

                AuditLogger::log($teacher, 'planning.draft_created', $planning, null, [
                    'title' => $planning->title,
                    'status' => $planning->status->value,
                    'version' => 1,
                ]);

                return $planning->fresh(['currentVersion', 'assignment', 'subject']);
            });
        } catch (\Throwable $e) {
            if ($storedPath && $disk->exists($storedPath)) {
                $disk->delete($storedPath);
            }
            throw $e;
        }
    }

    /**
     * Update an existing draft or rejected planning. Uploading a file creates a new version.
     */
    public function updateDraft(Planning $planning, User $teacher, array $data, ?UploadedFile $file = null): Planning
    {
        if ($planning->status === PlanningStatus::APPROVED) {
            throw ValidationException::withMessages([
                'status' => 'Una planificación aprobada es inmutable y no puede ser modificada.',
            ]);
        }

        if ($planning->status === PlanningStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Una planificación en revisión no puede ser modificada hasta su resolución.',
            ]);
        }

        $disk = Storage::disk('private_plannings');
        $storedPath = null;

        if ($file) {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $randomFileName = \Illuminate\Support\Str::random(40).'.'.$extension;
            $storedPath = $disk->putFileAs('', $file, $randomFileName);
            $fullPath = $disk->path($storedPath);
        }

        try {
            return DB::transaction(function () use ($planning, $teacher, $data, $file, $storedPath, $disk) {
                $p = Planning::where('id', $planning->id)->lockForUpdate()->first();

                $oldValues = [
                    'title' => $p->title,
                    'week_start' => $p->week_start?->toDateString(),
                    'week_end' => $p->week_end?->toDateString(),
                ];

                $updateData = [
                    'title' => $data['title'] ?? $p->title,
                    'week_start' => $data['week_start'] ?? $p->week_start,
                    'week_end' => $data['week_end'] ?? $p->week_end,
                ];

                if ($file && $storedPath) {
                    $fullPath = $disk->path($storedPath);
                    $checksum = hash_file('sha256', $fullPath);
                    $size = filesize($fullPath);
                    $mime = $file->getClientMimeType() ?: 'application/pdf';
                    $originalName = $file->getClientOriginalName();

                    $nextVersionNumber = (int) $p->versions()->max('version') + 1;

                    $newVersion = PlanningVersion::create([
                        'planning_id' => $p->id,
                        'version' => $nextVersionNumber,
                        'file_path' => $storedPath,
                        'file_disk' => 'private_plannings',
                        'original_name' => $originalName,
                        'mime' => $mime,
                        'size' => $size,
                        'checksum' => $checksum,
                        'uploaded_by' => $teacher->id,
                        'created_at' => now(),
                    ]);

                    $updateData['file_path'] = $storedPath;
                    $updateData['current_version_id'] = $newVersion->id;
                }

                $p->update($updateData);

                AuditLogger::log($teacher, 'planning.updated', $p, $oldValues, [
                    'title' => $p->title,
                    'current_version_id' => $p->current_version_id,
                ]);

                return $p->fresh(['currentVersion', 'assignment', 'subject']);
            });
        } catch (\Throwable $e) {
            if ($storedPath && $disk->exists($storedPath)) {
                $disk->delete($storedPath);
            }
            throw $e;
        }
    }

    /**
     * Submit planning to Vicerrectorado (draft -> pending or rejected -> pending).
     */
    public function submit(Planning $planning, User $teacher): Planning
    {
        return DB::transaction(function () use ($planning, $teacher) {
            $p = Planning::where('id', $planning->id)->lockForUpdate()->first();

            if ($p->status === PlanningStatus::APPROVED) {
                throw ValidationException::withMessages([
                    'status' => 'Una planificación aprobada no puede volver a enviarse.',
                ]);
            }

            if ($p->status === PlanningStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'La planificación ya se encuentra pendiente de revisión.',
                ]);
            }

            if ($p->status === PlanningStatus::REJECTED) {
                // Must have uploaded a newer version after rejection review
                $latestReview = $p->reviews()->where('decision', 'rejected')->latest('id')->first();
                if ($latestReview && $p->current_version_id === $latestReview->version_id) {
                    throw ValidationException::withMessages([
                        'file' => 'Debe cargar una nueva versión del documento antes de reenviar una planificación rechazada.',
                    ]);
                }
            }

            $fromStatus = $p->status->value;
            $p->update([
                'status' => PlanningStatus::PENDING,
                'submitted_at' => now(),
            ]);

            AuditLogger::log($teacher, 'planning.submitted', $p, ['status' => $fromStatus], ['status' => PlanningStatus::PENDING->value]);

            return $p->fresh();
        });
    }

    /**
     * Approve planning (pending -> approved).
     */
    public function approve(Planning $planning, User $reviewer): Planning
    {
        return DB::transaction(function () use ($planning, $reviewer) {
            $p = Planning::where('id', $planning->id)->lockForUpdate()->first();

            if ($p->status !== PlanningStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Solo se pueden aprobar planificaciones en estado pendiente.',
                ]);
            }

            $fromStatus = $p->status->value;
            $now = now();

            $p->update([
                'status' => PlanningStatus::APPROVED,
                'decided_at' => $now,
            ]);

            PlanningReview::create([
                'planning_id' => $p->id,
                'version_id' => $p->current_version_id,
                'reviewer_id' => $reviewer->id,
                'from_status' => $fromStatus,
                'to_status' => PlanningStatus::APPROVED->value,
                'decision' => 'approved',
                'comment' => null,
                'created_at' => $now,
            ]);

            AuditLogger::log($reviewer, 'planning.approved', $p, ['status' => $fromStatus], ['status' => PlanningStatus::APPROVED->value]);

            return $p->fresh();
        });
    }

    /**
     * Reject planning with required non-empty reason (pending -> rejected).
     */
    public function reject(Planning $planning, User $reviewer, string $comment): Planning
    {
        $cleanComment = trim($comment);
        if (empty($cleanComment)) {
            throw ValidationException::withMessages([
                'comment' => 'El motivo de rechazo es obligatorio.',
            ]);
        }

        return DB::transaction(function () use ($planning, $reviewer, $cleanComment) {
            $p = Planning::where('id', $planning->id)->lockForUpdate()->first();

            if ($p->status !== PlanningStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Solo se pueden rechazar planificaciones en estado pendiente.',
                ]);
            }

            $fromStatus = $p->status->value;
            $now = now();

            $p->update([
                'status' => PlanningStatus::REJECTED,
                'decided_at' => $now,
            ]);

            PlanningReview::create([
                'planning_id' => $p->id,
                'version_id' => $p->current_version_id,
                'reviewer_id' => $reviewer->id,
                'from_status' => $fromStatus,
                'to_status' => PlanningStatus::REJECTED->value,
                'decision' => 'rejected',
                'comment' => $cleanComment,
                'created_at' => $now,
            ]);

            AuditLogger::log($reviewer, 'planning.rejected', $p, ['status' => $fromStatus], [
                'status' => PlanningStatus::REJECTED->value,
                'comment' => $cleanComment,
            ]);

            return $p->fresh();
        });
    }
}
