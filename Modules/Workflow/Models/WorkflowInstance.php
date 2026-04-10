<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    use HasFactory;

    protected $table = 'workflow_instances';

    protected $fillable = [
        'workflowable_type',
        'workflowable_id',
        'initiated_by',
        'status',
        'current_stage',
    ];

    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class);
    }
}
