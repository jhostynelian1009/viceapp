<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class AcademicDocumentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('El archivo proporcionado no es válido.');

            return;
        }

        if (! $value->isValid()) {
            $fail('El archivo no se subió correctamente o está dañado.');

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mime = $value->getMimeType();
        $realPath = $value->getRealPath();

        // 1. Validar extensión permitida
        if (! in_array($extension, ['pdf', 'doc', 'docx'])) {
            $fail('Extensión de archivo no permitida. Solo se aceptan .pdf, .doc y .docx.');

            return;
        }

        // Leer los primeros bytes para firma binaria
        $handle = fopen($realPath, 'rb');
        if (! $handle) {
            $fail('No se pudo abrir el archivo para verificar su integridad.');

            return;
        }
        $header = fread($handle, 8);
        fclose($handle);

        $fileSize = filesize($realPath);
        if ($fileSize === 0) {
            $fail('El archivo está vacío.');

            return;
        }

        // 2. Validación por formato
        if ($extension === 'pdf') {
            // Validar MIME
            if ($mime !== 'application/pdf') {
                $fail('El tipo MIME no es coherente con un archivo PDF.');

                return;
            }
            // Validar firma de PDF (%PDF-)
            if (substr($header, 0, 5) !== '%PDF-') {
                $fail('El archivo no es un documento PDF válido.');

                return;
            }
        } elseif ($extension === 'doc') {
            // Validar MIME
            $allowedDocMimes = [
                'application/msword',
                'application/vnd.ms-office',
                'application/octet-stream',
                'application/x-ole-storage',
            ];
            if (! in_array($mime, $allowedDocMimes)) {
                $fail('El tipo MIME no es coherente con un archivo DOC.');

                return;
            }
            // Validar firma OLE Compound File (D0 CF 11 E0 A1 B1 1A E1)
            $expectedSignature = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
            if ($header !== $expectedSignature) {
                $fail('La firma del archivo no corresponde a un documento DOC binario válido.');

                return;
            }
        } elseif ($extension === 'docx') {
            // Validar MIME
            $allowedDocxMimes = [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/x-zip-compressed',
                'application/octet-stream',
            ];
            if (! in_array($mime, $allowedDocxMimes)) {
                $fail('El tipo MIME no es coherente con un archivo DOCX.');

                return;
            }

            // Validar estructura ZIP de Office Open XML
            $zip = new ZipArchive;
            if ($zip->open($realPath) === true) {
                // Verificar que no sea un zip vacío
                if ($zip->numFiles === 0) {
                    $zip->close();
                    $fail('El archivo ZIP está vacío.');

                    return;
                }

                // Validar presencia simultánea de [Content_Types].xml y word/document.xml
                $hasContentTypes = $zip->locateName('[Content_Types].xml') !== false;
                $hasWordDocument = $zip->locateName('word/document.xml') !== false;

                if (! $hasContentTypes || ! $hasWordDocument) {
                    $zip->close();
                    $fail('El archivo .docx no contiene la estructura XML requerida de Office.');

                    return;
                }

                // Verificar que no contenga macros o VBA
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    $entryName = $stat['name'];

                    // Evitar path traversals peligrosos
                    if (str_contains($entryName, '..') || str_starts_with($entryName, '/')) {
                        $zip->close();
                        $fail('El archivo contiene rutas de directorios no permitidas.');

                        return;
                    }

                    // Buscar elementos de macros (.bin o vbaProject)
                    if (str_ends_with(strtolower($entryName), '.bin') || str_contains(strtolower($entryName), 'vba')) {
                        $zip->close();
                        $fail('El archivo contiene macros (.docm o similar) y no está permitido.');

                        return;
                    }
                }

                $zip->close();
            } else {
                $fail('El archivo DOCX está corrupto o no se pudo abrir como un archivo ZIP válido.');

                return;
            }
        }
    }
}
