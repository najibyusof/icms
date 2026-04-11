<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'entity_type',
        'entity_id',
        'workflowable_type',
        'workflowable_id',
        'current_step_id',
        'current_stage',
        'status',
        'created_by',
        'initiated_by',
        'submitted_by',
        'submitted_at',
        'final_approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get associated workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get current step
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    /**
     * Get related entity (polymorphic)
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy relation retained for compatibility with older modules/tests.
     */
    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get creator user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get user who submitted
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get user who approved
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approved_by');
    }

    /**
     * Get user who rejected
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get all workflow logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowLog::class)
            ->orderByDesc('created_at');
    }

    /**
     * Check if workflow is in specific status
     */
    public function isStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if workflow is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if workflow can be edited
     */
    public function canEdit(): bool
    {
        return $this->isStatus('draft');
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->isStatus('approved')) {
            return 100;
        }

        if ($this->isStatus('rejected')) {
            return 0;
        }

        if (!$this->current_step_id) {
            return 0;
        }

        $totalSteps = $this->workflow->steps()->count();
        if ($totalSteps <= 0) {
            return 0;
        }

        $currentStepNumber = $this->currentStep?->step_number ?? 1;

        return intval(($currentStepNumber / $totalSteps) * 100);
    }
}
