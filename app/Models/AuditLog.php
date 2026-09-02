<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Prevent updating or deleting audit logs from the application.
     */
    public static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \RuntimeException('Audit logs are append-only and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \RuntimeException('Audit logs are append-only and cannot be deleted.');
        });
    }
}
