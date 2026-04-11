<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Notification\Events\EntityApproved;
use Modules\Notification\Events\EntityRejected;
use Modules\Notification\Events\EntitySubmitted;
use Modules\Notification\Listeners\DispatchEntityDecisionFromWorkflow;
use Modules\Notification\Listeners\DispatchEntitySubmittedFromWorkflow;
use Modules\Notification\Listeners\SendDatabaseNotification;
use Modules\Notification\Listeners\SendMailNotification;
use Modules\Notification\Listeners\SendPushNotification;
use Modules\Notification\Listeners\SendTelegramNotification;
use Modules\Workflow\Events\WorkflowDecisionRecorded;
use Modules\Workflow\Events\WorkflowSubmitted;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        WorkflowSubmitted::class => [
            DispatchEntitySubmittedFromWorkflow::class,
        ],
        WorkflowDecisionRecorded::class => [
            DispatchEntityDecisionFromWorkflow::class,
        ],
        EntitySubmitted::class => [
            SendDatabaseNotification::class,
            SendMailNotification::class,
            SendTelegramNotification::class,
            SendPushNotification::class,
        ],
        EntityApproved::class => [
            SendDatabaseNotification::class,
            SendMailNotification::class,
            SendTelegramNotification::class,
            SendPushNotification::class,
        ],
        EntityRejected::class => [
            SendDatabaseNotification::class,
            SendMailNotification::class,
            SendTelegramNotification::class,
            SendPushNotification::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
