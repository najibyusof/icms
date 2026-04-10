<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Workflow\Events\WorkflowDecisionRecorded;
use Modules\Workflow\Events\WorkflowSubmitted;
use Modules\Workflow\Listeners\SendWorkflowDecisionNotifications;
use Modules\Workflow\Listeners\SendWorkflowSubmittedNotifications;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        WorkflowSubmitted::class => [
            SendWorkflowSubmittedNotifications::class,
        ],
        WorkflowDecisionRecorded::class => [
            SendWorkflowDecisionNotifications::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
