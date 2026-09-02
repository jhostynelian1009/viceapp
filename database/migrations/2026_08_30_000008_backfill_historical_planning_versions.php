<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Map historical status to canonical string enum.
     */
    protected function mapCanonicalStatus(?string $status): string
    {
        if (! $status) {
            return 'draft';
        }

        $s = mb_strtolower(trim($status));

        return match ($s) {
            'aprobada', 'approved', 'aprobado' => 'approved',
            'rechazada', 'rejected', 'rechazado' => 'rejected',
            'pendiente', 'pending', 'en_revision', 'en revision' => 'pending',
            'borrador', 'draft' => 'draft',
            default => 'draft',
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

            // Check if version 1 already exists for this planning
            $version = DB::table('planning_versions')
                ->where('planning_id', $planning->id)
                ->where('version', 1)
                ->first();

            if (! $version) {
                $filePath = $planning->file_path;
                $fileDisk = 'private_plannings';

                $size = 0;
                $mime = 'application/octet-stream';
                $checksum = hash('sha256', '');
                $originalName = basename($filePath);

                // Attempt to read file metadata from private_plannings disk or quarantine disk
                if ($filePath && Storage::disk('private_plannings')->exists($filePath)) {
                    $size = Storage::disk('private_plannings')->size($filePath);
                    $mime = Storage::disk('private_plannings')->mimeType($filePath) ?: 'application/octet-stream';
                    $fullPath = Storage::disk('private_plannings')->path($filePath);
                    if (file_exists($fullPath)) {
                        $checksum = hash_file('sha256', $fullPath);
                    }
                } elseif ($filePath && Storage::disk('private')->exists('quarantine/'.basename($filePath))) {
                    $quarantineRel = 'quarantine/'.basename($filePath);
                    $size = Storage::disk('private')->size($quarantineRel);
                    $mime = Storage::disk('private')->mimeType($quarantineRel) ?: 'application/octet-stream';
                    $fullPath = Storage::disk('private')->path($quarantineRel);
                    if (file_exists($fullPath)) {
                        $checksum = hash_file('sha256', $fullPath);
                    }
                } else {
                    Log::warning("Migración K-006: Archivo no encontrado para planificación ID {$planning->id}: {$filePath}");
                }

                $versionId = DB::table('planning_versions')->insertGetId([
                    'planning_id' => $planning->id,
                    'version' => 1,
                    'file_path' => $filePath ?: 'missing_historical_file.pdf',
                    'file_disk' => $fileDisk,
                    'original_name' => $originalName ?: 'planificacion.pdf',
                    'mime' => $mime,
                    'size' => $size,
                    'checksum' => $checksum,
                    'uploaded_by' => $planning->user_id,
                    'created_at' => $planning->created_at ?: now(),
                ]);
            } else {
                $versionId = $version->id;
            }

            // Update planning record
            $updateData = [
                'status' => $canonicalStatus,
                'current_version_id' => $versionId,
            ];

            if ($canonicalStatus === 'pending') {
                $updateData['submitted_at'] = $planning->created_at ?: now();
            } elseif ($canonicalStatus === 'approved' || $canonicalStatus === 'rejected') {
                $updateData['submitted_at'] = $planning->created_at ?: now();
                $updateData['decided_at'] = $planning->updated_at ?: now();
            }

            DB::table('plannings')->where('id', $planning->id)->update($updateData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plannings')->update([
            'current_version_id' => null,
            'submitted_at' => null,
            'decided_at' => null,
        ]);
        DB::table('planning_versions')->delete();
    }
};
