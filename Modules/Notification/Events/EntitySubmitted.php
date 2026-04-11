<?php

namespace Modules\Notification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntitySubmitted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param array<int, int>|null $recipientIds
     * @param array<string, mixed>|null $meta
     */
    public function __construct(
        public readonly string $entityType,
        public readonly int $entityId,
        public readonly int $actorId,
        public readonly ?array $recipientIds = null,
        public readonly ?array $meta = null,
    ) {
    }
}
