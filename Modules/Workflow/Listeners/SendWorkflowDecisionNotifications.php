<?php

namespace Modules\Workflow\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\WorkflowStatusNotification;
use Modules\Workflow\Events\WorkflowDecisionRecorded;

class SendWorkflowDecisionNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 30;

    public function handle(WorkflowDecisionRecorded $event): void
    {
        $workflow = $event->workflow->fresh(['workflowable']);

        $audience = User::query()
            ->whereKey([$workflow->initiated_by, $event->approval->reviewer_id])
            ->get();

        foreach ($audience as $user) {
            $user->notify(new WorkflowStatusNotification($workflow, 'Workflow Decision Recorded'));
        }
    }
}
