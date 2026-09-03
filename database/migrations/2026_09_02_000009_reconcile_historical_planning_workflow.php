<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Map historical status to canonical string enum strictly.
     * Throws RuntimeException if unknown status encountered.
     */
    protected function mapCanonicalStatus(?string $status): string
    {
        if ($status === null || trim($status) === '') {
            return 'draft';
        }

        return match (strtolower(trim($status))) {
            'aprobada', 'approved', 'aprobado' => 'approved',
            'rechazada', 'rejected', 'rechazado' => 'rejected',
            'pendiente', 'pending', 'en_revision', 'en revision' => 'pending',
            'borrador', 'draft' => 'draft',
            default => throw new \RuntimeException("Estado histórico desconocido o no permitido para planificación: '{$status}'"),
        };
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $plannings = DB::table('plannings')->get();

        foreach ($plannings as $planning) {
            $canonicalStatus = $this->mapCanonicalStatus($planning->status);

            // Verify if version 1 already exists
            $existingVersion = DB::table('planning_versions')
                ->where('planning_id', $planning->id)
                ->orderBy('version', 'asc')
                ->first();

            $versionId = $existingVersion?->id;

            if (! $existingVersion) {
                $filePath = $planning->file_path;
                $fileDisk = 'private_plannings';

                $size = 0;
                $mime = 'application/octet-stream';
                $checksum = '0000000000000000000000000000000000000000000000000000000000000000';
                $originalName = $filePath ? basename($filePath) : 'planificacion.pdf';

                if ($filePath && Storage::disk('private_plannings')->exists($filePath)) {
                    $size = Storage::disk('private_plannings')->size($filePath);
                    $mime = Storage::disk('private_plannings')->mimeType($filePath) ?: $mime;
                    $fullPath = Storage::disk('private_plannings')->path($filePath);
                    if (file_exists($fullPath)) {
                        $checksum = hash_file('sha256', $fullPath);
                    }
                } elseif ($filePath && Storage::disk('local')->exists('private/quarantine/'.basename($filePath))) {
                    $quarantineRelPath = 'private/quarantine/'.basename($filePath);
                    $fileDisk = 'local';
                    $size = Storage::disk('local')->size($quarantineRelPath);
                    $mime = Storage::disk('local')->mimeType($quarantineRelPath) ?: $mime;
                    $fullPath = Storage::disk('local')->path($quarantineRelPath);
                    if (file_exists($fullPath)) {
                        $checksum = hash_file('sha256', $fullPath);
                    }
                } else {
                    Log::error("ANOMALÍA K-006 (000009): Archivo físico no encontrado para la planificación ID {$planning->id}: '{$filePath}'");
                }

                $versionId = DB::table('planning_versions')->insertGetId([
                    'planning_id' => $planning->id,
                    'version' => 1,
                    'file_path' => $filePath ?: 'missing_historical_file.pdf',
                    'file_disk' => $fileDisk,
                    'original_name' => $originalName,
                    'mime' => $mime,
                    'size' => $size,
                    'checksum' => $checksum,
                    'uploaded_by' => $planning->user_id,
                    'created_at' => $planning->created_at ?: now(),
                ]);
            }

            $updateData = [
                'status' => $canonicalStatus,
            ];

            if ($planning->current_version_id === null && $versionId) {
                $updateData['current_version_id'] = $versionId;
            }

            if ($canonicalStatus === 'pending') {
                $updateData['submitted_at'] = $planning->created_at;
            } elseif ($canonicalStatus === 'approved' || $canonicalStatus === 'rejected') {
                $updateData['submitted_at'] = $planning->created_at;
                $updateData['decided_at'] = $planning->updated_at;
            }

            DB::table('plannings')->where('id', $planning->id)->update($updateData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException('Reconciling historical planning workflow is irreversible without a database backup.');
    }
};
