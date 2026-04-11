<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Events\EntitySubmitted;
use Modules\Workflow\Events\WorkflowSubmitted;

class DispatchEntitySubmittedFromWorkflow implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 30;

    public function handle(WorkflowSubmitted $event): void
    {
        $workflow = $event->workflow;

        event(new EntitySubmitted(
            entityType: (string) $workflow->entity_type,
            entityId: (int) $workflow->entity_id,
            actorId: (int) ($workflow->submitted_by ?? $workflow->created_by ?? 0),
            recipientIds: null,
            meta: [
                'workflow_id' => $workflow->id,
                'status' => $workflow->status,
                'current_step_id' => $workflow->current_step_id,
            ],
        ));
    }
}
