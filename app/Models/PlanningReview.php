<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningReview extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'planning_id',
        'version_id',
        'reviewer_id',
        'from_status',
        'to_status',
        'decision',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function planning(): BelongsTo
    {
        return $this->belongsTo(Planning::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(PlanningVersion::class, 'version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
