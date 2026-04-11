<?php

namespace Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'entity_type',
        'is_active',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all steps for this workflow
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)
            ->orderBy('step_number');
    }

    /**
     * Get all instances of this workflow
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Get first step of workflow
     */
    public function getFirstStep(): ?WorkflowStep
    {
        return $this->steps()->first();
    }

    /**
     * Get next step after given step
     */
    public function getNextStep(WorkflowStep $currentStep): ?WorkflowStep
    {
        return $this->steps()
            ->where('step_number', '>', $currentStep->step_number)
            ->first();
    }

    /**
     * Get step by approval level
     */
    public function getStepByApprovalLevel(int $level): ?WorkflowStep
    {
        return $this->steps()
            ->where('approval_level', $level)
            ->first();
    }
}
