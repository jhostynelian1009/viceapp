<?php

namespace App\Console\Commands;

use App\Models\Planning;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigratePrivateDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:private-documents {--force : Execute actual file migration instead of dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate planning documents from public storage to private storage and quarantine orphans securely';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = ! $this->option('force');

        $this->info('=== MIGRACIÓN SEGURA DE DOCUMENTOS A ALMACENAMIENTO PRIVADO ===');
        if ($isDryRun) {
            $this->warn('Modo DRY-RUN activo. No se realizarán cambios en los archivos. Usa --force para ejecutar.');
        } else {
            $this->info('Modo EJECUCIÓN activo. Los archivos serán movidos y verificados.');
        }

        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('private_plannings');
        $privateBaseDisk = Storage::disk('private');

        $publicFiles = $publicDisk->allFiles('plannings');
        $this->info('Archivos encontrados en almacenamiento público: '.count($publicFiles));

        $associatedCount = 0;
        $quarantinedCount = 0;
        $alreadyMigratedCount = 0;
        $missingCount = 0;
        $errors = [];
        $quarantineManifest = [];

        // 1. Revisar registros en BD
        $plannings = Planning::all();
        $dbFilePaths = $plannings->pluck('file_path')->filter()->toArray();

        // 2. Procesar cada archivo en el almacenamiento público
        foreach ($publicFiles as $publicFile) {
            if (! $publicDisk->exists($publicFile)) {
                continue;
            }

            $content = $publicDisk->get($publicFile);
            $size = $publicDisk->size($publicFile);
            $sha256 = hash('sha256', $content);
            $filename = basename($publicFile);

            $isAssociated = in_array($publicFile, $dbFilePaths) || in_array($filename, $dbFilePaths);

            if ($isAssociated) {
                // Documento asociado a registro en BD
                if ($privateDisk->exists($filename)) {
                    $destContent = $privateDisk->get($filename);
                    $destSha256 = hash('sha256', $destContent);
                    if ($destSha256 !== $sha256) {
                        $msg = "COLISIÓN DETECTADA: El archivo destino {$filename} existe en privado con un checksum diferente.";
                        $this->error($msg);
                        $errors[] = $msg;

                        return 1;
                    }
                    $this->line("Archivo {$filename} ya migrado previamente y verificado.");
                    $alreadyMigratedCount++;
                    if (! $isDryRun) {
                        $publicDisk->delete($publicFile);
                    }

                    continue;
                }

                $this->info("Migrando archivo asociado a BD: {$filename}");
                $associatedCount++;

                if (! $isDryRun) {
                    $privateDisk->put($filename, $content);
                    $newContent = $privateDisk->get($filename);
                    $newSha256 = hash('sha256', $newContent);
                    if ($newSha256 !== $sha256 || strlen($newContent) !== $size) {
                        $msg = "ERROR DE VERIFICACIÓN: El archivo {$filename} copiado no coincide en tamaño o SHA-256.";
                        $this->error($msg);
                        $errors[] = $msg;
                        $privateDisk->delete($filename);

                        return 1;
                    }
                    // Eliminar origen público solo después de verificar
                    $publicDisk->delete($publicFile);
                }
            } else {
                // Documento huérfano sin registro en BD -> Cuarentena
                $quarantineRelPath = 'quarantine/'.$filename;
                $this->warn("Cuarentena de archivo huérfano sin BD: {$filename}");
                $quarantinedCount++;

                $quarantineManifest[$filename] = [
                    'original_public_path' => $publicFile,
                    'quarantine_path' => 'private/quarantine/'.$filename,
                    'size' => $size,
                    'sha256' => $sha256,
                    'status' => 'orphan_without_database_record',
                    'quarantined_at' => date('Y-m-d H:i:s'),
                ];

                if (! $isDryRun) {
                    if ($privateBaseDisk->exists($quarantineRelPath)) {
                        $qContent = $privateBaseDisk->get($quarantineRelPath);
                        $qSha256 = hash('sha256', $qContent);
                        if ($qSha256 !== $sha256) {
                            $msg = "COLISIÓN EN CUARENTENA: El archivo {$filename} existe en cuarentena con diferente checksum.";
                            $this->error($msg);
                            $errors[] = $msg;

                            return 1;
                        }
                    } else {
                        $privateBaseDisk->put($quarantineRelPath, $content);
                        $qContent = $privateBaseDisk->get($quarantineRelPath);
                        $qSha256 = hash('sha256', $qContent);
                        if ($qSha256 !== $sha256 || strlen($qContent) !== $size) {
                            $msg = "ERROR DE CUARENTENA: Copia de {$filename} falló la verificación de checksum.";
                            $this->error($msg);
                            $errors[] = $msg;
                            $privateBaseDisk->delete($quarantineRelPath);

                            return 1;
                        }
                    }
                    $publicDisk->delete($publicFile);
                }
            }
        }

        // 3. Revisar si hay registros en BD cuyo archivo físico no exista
        foreach ($plannings as $planning) {
            $path = $planning->file_path;
            $existsInPrivate = $privateDisk->exists($path) || $privateDisk->exists(basename($path));
            $existsInPublic = $publicDisk->exists($path);
            if (! $existsInPrivate && ! $existsInPublic) {
                $missingCount++;
                $this->error("REGISTRO FALTANTE EN BD: Planificación ID {$planning->id} exige archivo '{$path}' pero no se encuentra físicamente.");
            }
        }

        // Guardar manifiesto de cuarentena
        if (! $isDryRun && count($quarantineManifest) > 0) {
            $manifestRelPath = 'quarantine/manifest.json';
            $existingManifest = $privateBaseDisk->exists($manifestRelPath)
                ? json_decode($privateBaseDisk->get($manifestRelPath), true)
                : [];
            $finalManifest = array_merge($existingManifest, $quarantineManifest);
            $privateBaseDisk->put($manifestRelPath, json_encode($finalManifest, JSON_PRETTY_PRINT));
            $this->info("Manifiesto de cuarentena guardado en: storage/app/private/{$manifestRelPath}");
        }

        $this->newLine();
        $this->info('=== RESUMEN FINAL DE MIGRACIÓN ===');
        $this->line("Asociados migrados: {$associatedCount}");
        $this->line("Huérfanos en cuarentena: {$quarantinedCount}");
        $this->line("Ya migrados anteriormente: {$alreadyMigratedCount}");
        $this->line("Registros sin archivo físico: {$missingCount}");
        $this->line('Errores detectados: '.count($errors));

        if ($isDryRun) {
            $this->warn('Recuerda ejecutar php artisan migrate:private-documents --force para aplicar los cambios.');
        } else {
            $this->info('Migración completada exitosamente.');
        }

        return 0;
    }
}
