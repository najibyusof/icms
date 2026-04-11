<?php

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Notification\Events\EntityApproved;
use Modules\Notification\Events\EntityRejected;
use Modules\Notification\Events\EntitySubmitted;
use Modules\Notification\Services\NotificationService;

class SendPushNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(EntitySubmitted|EntityApproved|EntityRejected $event): void
    {
        $state = $this->resolveState($event);
        $payload = $this->notificationService->payload(
            $state,
            $event->entityType,
            $event->entityId,
            $event->actorId,
            $event->meta ?? [],
        );

        $recipients = $this->notificationService->resolveRecipients($event->recipientIds, $event->actorId);

        foreach ($recipients as $recipient) {
            $this->notificationService->sendPush($recipient, $payload);
        }
    }

    private function resolveState(EntitySubmitted|EntityApproved|EntityRejected $event): string
    {
        return match (true) {
            $event instanceof EntitySubmitted => 'submitted',
            $event instanceof EntityApproved => 'approved',
            $event instanceof EntityRejected => 'rejected',
        };
    }
}
