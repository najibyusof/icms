<?php

namespace Modules\Workflow\DTOs;

readonly class WorkflowDecisionDTO
{
    public function __construct(
        public int $workflowId,
        public int $reviewerId,
        public string $decision,
        public ?string $comments,
    ) {
    }

    public static function fromArray(array $payload, int $reviewerId): self
    {
        return new self(
            workflowId: (int) $payload['workflow_id'],
            reviewerId: $reviewerId,
            decision: $payload['decision'],
            comments: $payload['comments'] ?? null,
        );
    }
}
