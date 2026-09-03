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
        // 1. Change column default of integrity_status to 'unknown_legacy_metadata'
        Schema::table('planning_versions', function (Blueprint $table) {
            $table->string('integrity_status', 50)->default('unknown_legacy_metadata')->change();
        });

        // 2. Perform strict reconciliation on existing version records within transaction
        DB::transaction(function () {
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

                $hasSyntheticHash = ($version->checksum === '0000000000000000000000000000000000000000000000000000000000000000');
                $hasZeroSize = ((int) $version->size === 0);
                $hasGenericMime = ($version->mime === 'application/octet-stream');
                $hasSyntheticName = in_array($version->original_name, ['planificacion.pdf', 'missing_historical_file.pdf'], true) || ($version->original_name === basename($filePath));

                if ($fullPath && file_exists($fullPath)) {
                    $realSize = filesize($fullPath);
                    $realChecksum = hash_file('sha256', $fullPath);
                    $realMime = mime_content_type($fullPath) ?: 'application/pdf';

                    $checksumMatches = ($version->checksum !== null && $version->checksum === $realChecksum && strlen($version->checksum) === 64 && ! $hasSyntheticHash);
                    $sizeMatches = ($version->size !== null && (int) $version->size === $realSize && ! $hasZeroSize);

                    if ($checksumMatches && $sizeMatches && ! $hasGenericMime) {
                        // Strictly verified
                        DB::table('planning_versions')
                            ->where('id', $version->id)
                            ->update([
                                'integrity_status' => 'verified',
                                'integrity_verified_at' => $version->integrity_verified_at ?: now(),
                                'original_name' => $hasSyntheticName ? null : $version->original_name,
                            ]);
                    } elseif ($hasSyntheticHash || $hasZeroSize || $hasGenericMime) {
                        // Legacy record with synthetic/unverified metadata -> unknown_legacy_metadata
                        DB::table('planning_versions')
                            ->where('id', $version->id)
                            ->update([
                                'checksum' => null,
                                'size' => null,
                                'mime' => null,
                                'original_name' => $hasSyntheticName ? null : $version->original_name,
                                'integrity_status' => 'unknown_legacy_metadata',
                                'integrity_verified_at' => null,
                            ]);
                    } else {
                        // File exists but metadata does not match physical evidence -> unknown_legacy_metadata
                        DB::table('planning_versions')
                            ->where('id', $version->id)
                            ->update([
                                'integrity_status' => 'unknown_legacy_metadata',
                                'integrity_verified_at' => null,
                            ]);
                    }
                } else {
                    // Physical file missing -> missing_file
                    DB::table('planning_versions')
                        ->where('id', $version->id)
                        ->update([
                            'checksum' => null,
                            'size' => null,
                            'mime' => null,
                            'original_name' => $hasSyntheticName ? null : $version->original_name,
                            'integrity_status' => 'missing_file',
                            'integrity_verified_at' => null,
                        ]);

                    Log::error("ANOMALÍA K-006 (000011): Archivo físico ausente para planning_version ID {$version->id} (Planning ID {$version->planning_id}): '{$filePath}'");
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException('Reconciling legacy planning version metadata default is irreversible without a database backup.');
    }
};
