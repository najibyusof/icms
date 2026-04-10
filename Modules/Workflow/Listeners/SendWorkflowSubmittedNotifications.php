<?php

namespace Modules\Workflow\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Notifications\WorkflowStatusNotification;
use Modules\Workflow\Events\WorkflowSubmitted;

class SendWorkflowSubmittedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 30;

    public function handle(WorkflowSubmitted $event): void
    {
        $reviewers = User::role(['Reviewer', 'Approver', 'reviewer', 'approver'])->get();

        foreach ($reviewers as $reviewer) {
            $reviewer->notify(new WorkflowStatusNotification($event->workflow, 'Workflow Submitted'));
        }
    }
}
