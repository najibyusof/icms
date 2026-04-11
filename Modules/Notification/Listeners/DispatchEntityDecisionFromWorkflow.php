<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Events\EntityApproved;
use Modules\Notification\Events\EntityRejected;
use Modules\Workflow\Events\WorkflowDecisionRecorded;

class DispatchEntityDecisionFromWorkflow implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 30;

    public function handle(WorkflowDecisionRecorded $event): void
    {
        $workflow = $event->workflow;

        $payload = [
            'entityType' => (string) $workflow->entity_type,
            'entityId' => (int) $workflow->entity_id,
            'actorId' => (int) ($event->approval->reviewer_id ?? 0),
            'recipientIds' => array_values(array_unique(array_filter([
                $workflow->created_by,
                $workflow->submitted_by,
            ]))),
            'meta' => [
                'workflow_id' => $workflow->id,
                'status' => $workflow->status,
                'approval_id' => $event->approval->id,
                'approval_status' => $event->approval->status,
                'comment' => $event->approval->comments,
            ],
        ];

        if ($workflow->status === 'approved') {
            event(new EntityApproved(...$payload));
            return;
        }

        if ($workflow->status === 'rejected') {
            event(new EntityRejected(...$payload));
        }
    }
}
