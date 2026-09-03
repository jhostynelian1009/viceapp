<?php

namespace App\Models;

use App\Enums\PlanningStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planning extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'status',
        'subject_id',
        'assignment_id',
        'week_start',
        'week_end',
        'current_version_id',
        'submitted_at',
        'decided_at',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Planning $planning) {
            if ($planning->current_version_id !== null) {
                $version = PlanningVersion::find($planning->current_version_id);
                if ($version && (int) $version->planning_id !== (int) $planning->id) {
                    throw new \InvalidArgumentException("La versión actual {$planning->current_version_id} no pertenece a la planificación {$planning->id}.");
                }
            }
        });
    }

    protected function status(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value instanceof PlanningStatus ? $value : PlanningStatus::tryFromLegacy($value),
            set: fn ($value) => $value instanceof PlanningStatus ? $value->value : (PlanningStatus::tryFromLegacy($value)?->value ?? $value)
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'assignment_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(PlanningVersion::class, 'current_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PlanningVersion::class, 'planning_id')->orderBy('version', 'asc');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PlanningReview::class, 'planning_id')->orderBy('id', 'asc');
    }
}
