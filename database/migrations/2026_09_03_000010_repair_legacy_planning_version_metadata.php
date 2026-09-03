<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modify schema to make legacy metadata columns nullable and add integrity tracking
        Schema::table('planning_versions', function (Blueprint $table) {
            $table->string('original_name')->nullable()->change();
            $table->string('mime', 100)->nullable()->change();
            $table->unsignedBigInteger('size')->nullable()->change();
            $table->string('checksum', 64)->nullable()->change();

            if (! Schema::hasColumn('planning_versions', 'integrity_status')) {
                $table->string('integrity_status', 50)->default('verified')->after('checksum');
            }
            if (! Schema::hasColumn('planning_versions', 'integrity_verified_at')) {
                $table->timestamp('integrity_verified_at')->nullable()->after('integrity_status');
            }
        });

        // 2. Perform honest reconciliation on historical versions within transaction if supported
        DB::transaction(function () {
            // Reconcile suspicious legacy planning versions
            $versions = DB::table('planning_versions')->get();

            foreach ($versions as $version) {
                $filePath = $version->file_path;
                $fileDisk = $version->file_disk ?: 'private_plannings';

                $fullPath = null;
                if ($filePath && Storage::disk($fileDisk)->exists($filePath)) {
                    $fullPath = Storage::disk($fileDisk)->path($filePath);
                } elseif ($filePath && Storage::disk('local')->exists('private/quarantine/'.basename($filePath))) {
                    $fullPath = Storage::disk('local')->path('private/quarantine/'.basename($filePath));
                }

                $isSuspicious = (
                    $version->checksum === '0000000000000000000000000000000000000000000000000000000000000000' ||
                    (int) $version->size === 0 ||
                    $version->mime === 'application/octet-stream' ||
                    in_array($version->original_name, ['planificacion.pdf', 'missing_historical_file.pdf'], true)
                );

                if ($fullPath && file_exists($fullPath)) {
                    // Physical file exists: compute real metadata honestly
                    $realSize = filesize($fullPath);
                    $realChecksum = hash_file('sha256', $fullPath);
                    $realMime = mime_content_type($fullPath) ?: 'application/pdf';

                    $hasTrustworthyOriginalName = ($version->original_name && ! in_array($version->original_name, ['planificacion.pdf', 'missing_historical_file.pdf'], true) && $version->original_name !== basename($filePath));

                    DB::table('planning_versions')
                        ->where('id', $version->id)
                        ->update([
                            'size' => $realSize,
                            'checksum' => $realChecksum,
                            'mime' => $realMime,
                            'original_name' => $hasTrustworthyOriginalName ? $version->original_name : null,
                            'integrity_status' => 'verified',
                            'integrity_verified_at' => now(),
                        ]);
                } else {
                    // Physical file missing: preserve record without fake metadata
                    DB::table('planning_versions')
                        ->where('id', $version->id)
                        ->update([
                            'size' => null,
                            'checksum' => null,
                            'mime' => null,
                            'original_name' => null,
                            'integrity_status' => 'missing_file',
                            'integrity_verified_at' => null,
                        ]);

                    Log::error("ANOMALÍA K-006 (000010): Archivo físico ausente para planning_version ID {$version->id} (Planning ID {$version->planning_id}): '{$filePath}'");
                }
            }

            // Audit planning status integrity
            $plannings = DB::table('plannings')->get();
            $validStatuses = ['draft', 'pending', 'approved', 'rejected'];

            foreach ($plannings as $planning) {
                if ($planning->status === null || trim((string) $planning->status) === '' || ! in_array(strtolower(trim($planning->status)), $validStatuses, true)) {
                    Log::error("ANOMALÍA CRÍTICA K-006 (000010): Planificación ID {$planning->id} posee estado no válido o sin evidencia: '{$planning->status}'");
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException('Reconciling legacy planning version metadata is irreversible without a database backup.');
    }
};
