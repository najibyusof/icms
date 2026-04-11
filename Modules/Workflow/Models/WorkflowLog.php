<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_instance_id',
        'workflow_step_id',
        'user_id',
        'action',
        'comment',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    const UPDATED_AT = null;

    /**
     * Get associated workflow instance
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * Get associated workflow step
     */
    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * Get user who performed action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'submitted' => 'Submitted for Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'commented' => 'Added Comment',
            'clarification_requested' => 'Requested Clarification',
            'returned' => 'Returned to Submitter',
            'withdrawn' => 'Withdrawn',
            default => $this->action,
        };
    }
}
