<?php

namespace Modules\Workflow\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Workflow\DTOs\WorkflowDecisionDTO;
use Modules\Workflow\Events\WorkflowDecisionRecorded;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;

class WorkflowService
{
    /**
     * @return Collection<int, WorkflowInstance>
     */
    public function listPendingForUser(int $userId): Collection
    {
        return WorkflowInstance::query()
            ->with([
                'workflowable',
                'approvals' => fn ($query) => $query->orderBy('stage'),
            ])
            ->whereHas('approvals', function ($query) use ($userId): void {
                $query->where('reviewer_id', $userId)->where('status', 'pending');
            })
            ->get();
    }

    public function recordDecision(WorkflowDecisionDTO $dto): WorkflowInstance
    {
        return DB::transaction(function () use ($dto): WorkflowInstance {
            $workflow = WorkflowInstance::query()
                ->with(['approvals', 'workflowable'])
                ->findOrFail($dto->workflowId);

            $approval = WorkflowApproval::query()
                ->where('workflow_instance_id', $workflow->id)
                ->where('reviewer_id', $dto->reviewerId)
                ->where('status', 'pending')
                ->where('stage', $workflow->current_stage)
                ->firstOrFail();

            $approval->update([
                'status' => $dto->decision,
                'comments' => $dto->comments,
                'acted_at' => now(),
            ]);

            if ($dto->decision === 'approved') {
                WorkflowApproval::query()
                    ->where('workflow_instance_id', $workflow->id)
                    ->where('stage', '>', $approval->stage)
                    ->where('status', 'queued')
                    ->orderBy('stage')
                    ->limit(1)
                    ->update(['status' => 'pending']);
            }

            $pendingStage = WorkflowApproval::query()
                ->where('workflow_instance_id', $workflow->id)
                ->where('status', 'pending')
                ->min('stage');

            $workflow->update([
                'status' => $dto->decision === 'rejected' ? 'rejected' : ($pendingStage ? 'in_review' : 'approved'),
                'current_stage' => $pendingStage,
            ]);

            if ($workflow->workflowable && method_exists($workflow->workflowable, 'update')) {
                $workflow->workflowable->update([
                    'status' => $workflow->status,
                ]);
            }

            event(new WorkflowDecisionRecorded($workflow->fresh(['workflowable', 'approvals']), $approval));

            return $workflow->fresh(['workflowable', 'approvals']);
        });
    }
}
