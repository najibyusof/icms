<?php

namespace Modules\Workflow\Policies;

use App\Models\User;
use Modules\Workflow\Models\WorkflowInstance;

class WorkflowInstancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('workflow.view');
    }

    public function decide(User $user, WorkflowInstance $workflow): bool
    {
        return $user->can('workflow.review')
            && $workflow->approvals()
                ->where('reviewer_id', $user->id)
                ->where('status', 'pending')
                ->exists();
    }
}
