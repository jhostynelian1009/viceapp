<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'planning_id',
        'version',
        'file_path',
        'file_disk',
        'original_name',
        'mime',
        'size',
        'checksum',
        'integrity_status',
        'integrity_verified_at',
        'uploaded_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'integrity_verified_at' => 'datetime',
        'version' => 'integer',
        'size' => 'integer',
    ];

    public function isVerified(): bool
    {
        return $this->integrity_status === 'verified';
    }

    public function isMissingFile(): bool
    {
        return $this->integrity_status === 'missing_file';
    }

    public function isUnknownLegacyMetadata(): bool
    {
        return $this->integrity_status === 'unknown_legacy_metadata';
    }

    public function planning(): BelongsTo
    {
        return $this->belongsTo(Planning::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
