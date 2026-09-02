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
        'uploaded_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'version' => 'integer',
        'size' => 'integer',
    ];

    public function planning(): BelongsTo
    {
        return $this->belongsTo(Planning::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
