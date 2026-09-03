<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Record an audit log entry safely.
     */
    public static function log(?User $actor, string $event, Model $auditable, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        // Sanitize sensitive keys and values if any exist in arrays
        $sanitizedOld = self::sanitize($oldValues);
        $sanitizedNew = self::sanitize($newValues);

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'event' => $event,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $sanitizedOld,
            'new_values' => $sanitizedNew,
            'created_at' => now(),
        ]);
    }

    /**
     * Remove sensitive keys and sensitive value patterns (passwords, tokens, absolute paths, SQL, etc.).
     */
    public static function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $hiddenKeys = [
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'secret',
            'authorization',
            'auth_header',
            'content',
            'file',
            'binary',
            'file_content',
            'document_content',
            'file_path',
        ];

        $sanitized = [];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $hiddenKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } elseif (is_string($value)) {
                // Strip absolute file paths (Windows e.g., C:\... or Unix e.g., /var/...)
                if (preg_match('/^[A-Z]:\\\\|^\\/storage|^\\/var|^\\/tmp/i', $value)) {
                    $sanitized[$key] = basename($value);
                } elseif (preg_match('/SELECT|INSERT|UPDATE|DELETE|DROP|TRUNCATE/i', $value)) {
                    // Strip raw SQL statements
                    $sanitized[$key] = '[SQL Query Redacted]';
                } elseif (str_contains($value, 'Stack trace:') || str_contains($value, 'Exception:')) {
                    // Strip exception stack traces
                    $sanitized[$key] = '[Exception Redacted]';
                } else {
                    $sanitized[$key] = $value;
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
