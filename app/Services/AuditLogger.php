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
        // Sanitize sensitive keys if any exist in arrays
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
     * Remove sensitive keys (password, token, etc.) from log payloads.
     */
    protected static function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $hiddenKeys = ['password', 'remember_token', 'token', 'secret', 'content', 'file'];

        foreach ($hiddenKeys as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
