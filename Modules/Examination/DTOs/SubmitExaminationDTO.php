<?php

namespace Modules\Examination\DTOs;

readonly class SubmitExaminationDTO
{
    public function __construct(
        public int $courseId,
        public int $groupId,
        public int $submittedBy,
        public string $title,
        public string $examDate,
        public array $metadata = [],
    ) {
    }

    public static function fromArray(array $payload, int $submittedBy): self
    {
        return new self(
            courseId: (int) $payload['course_id'],
            groupId: (int) $payload['group_id'],
            submittedBy: $submittedBy,
            title: $payload['title'],
            examDate: $payload['exam_date'],
            metadata: $payload['metadata'] ?? [],
        );
    }
}
