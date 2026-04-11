<?php

namespace Modules\Jsu\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Course\Models\Course;
use Modules\Workflow\Models\WorkflowInstance;

class Jsu extends Model
{
    use SoftDeletes;

    protected $table = 'jsu';

    protected $fillable = [
        'course_id',
        'created_by',
        'approved_by',
        'activated_by',
        'academic_session',
        'exam_type',
        'title',
        'total_questions',
        'total_marks',
        'duration_minutes',
        'status',
        'difficulty_config',
        'notes',
        'approved_at',
        'activated_at',
    ];

    protected $casts = [
        'difficulty_config' => 'array',
        'approved_at'       => 'datetime',
        'activated_at'      => 'datetime',
        'total_questions'   => 'integer',
        'total_marks'       => 'integer',
        'duration_minutes'  => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function blueprints(): HasMany
    {
        return $this->hasMany(JsuBlueprint::class)->orderBy('question_no');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(JsuLog::class)->orderByDesc('created_at');
    }

    /** Polymorphic link to the new workflow system */
    public function workflowInstance(): MorphOne
    {
        return $this->morphOne(WorkflowInstance::class, 'entity');
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isSubmitted(): bool  { return $this->status === 'submitted'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function isActive(): bool     { return $this->status === 'active'; }

    public function canSubmit(): bool       { return $this->isDraft() || $this->isRejected(); }
    public function canActivate(): bool     { return $this->isApproved(); }

    // ── Difficulty config helpers ─────────────────────────────────────────────

    /**
     * Return the effective difficulty distribution (per-JSU override → global config).
     *
     * @return array<string, array{bloom_levels: int[], target_pct: int}>
     */
    public function effectiveDifficultyConfig(): array
    {
        if (!empty($this->difficulty_config)) {
            return $this->difficulty_config;
        }

        return config('jsu.difficulty_distribution');
    }
}
