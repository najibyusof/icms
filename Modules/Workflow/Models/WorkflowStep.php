<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'step_number',
        'title',
        'description',
        'roles_required',
        'approval_level',
        'action_type',
        'allow_rejection',
        'requires_comment',
    ];

    protected $casts = [
        'roles_required' => 'array',
        'allow_rejection' => 'boolean',
        'requires_comment' => 'boolean',
    ];

    /**
     * Get associated workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get all logs for this step
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowLog::class, 'workflow_step_id');
    }

    /**
     * Check if user has required role
     */
    public function userHasRequiredRole(?array $userRoles): bool
    {
        if (empty($this->roles_required)) {
            return true;
        }

        if (empty($userRoles)) {
            return false;
        }

        return count(array_intersect($this->roles_required, $userRoles)) > 0;
    }
}
