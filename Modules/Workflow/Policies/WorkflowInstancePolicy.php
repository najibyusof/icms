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

    public function view(User $user, WorkflowInstance $workflow): bool
    {
        return $user->can('workflow.view') || $workflow->created_by === $user->id;
    }

    public function submit(User $user, WorkflowInstance $workflow): bool
    {
        return $user->can('workflow.submit') || $workflow->created_by === $user->id;
    }

    public function comment(User $user, WorkflowInstance $workflow): bool
    {
        return $this->view($user, $workflow) || $this->decide($user, $workflow);
    }

    public function withdraw(User $user, WorkflowInstance $workflow): bool
    {
        return $workflow->created_by === $user->id && in_array($workflow->status, ['draft', 'in_progress'], true);
    }

    public function decide(User $user, WorkflowInstance $workflow): bool
    {
        $userRoles = $user->roles()->pluck('name')->toArray();

        return $user->can('workflow.review')
            && $workflow->status === 'in_progress'
            && $workflow->currentStep?->userHasRequiredRole($userRoles);
    }
}
