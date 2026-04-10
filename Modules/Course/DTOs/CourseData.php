<?php

namespace Modules\Course\DTOs;

use Illuminate\Http\Request;

readonly class CourseData
{
    /**
     * @param array<int, array<string, mixed>> $clos
     * @param array<int, array<string, mixed>> $requisites
     * @param array<int, array<string, mixed>> $assessments
     * @param array<int, array<string, mixed>> $topics
     * @param array<int, array<string, mixed>> $slt
     */
    public function __construct(
        public int $programmeId,
        public ?int $lecturerId,
        public ?int $resourcePersonId,
        public ?int $vetterId,
        public string $code,
        public string $name,
        public int $creditHours,
        public bool $isActive,
        public array $clos,
        public array $requisites,
        public array $assessments,
        public array $topics,
        public array $slt,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            programmeId: (int) $request->integer('programme_id'),
            lecturerId: $request->filled('lecturer_id') ? (int) $request->integer('lecturer_id') : null,
            resourcePersonId: $request->filled('resource_person_id') ? (int) $request->integer('resource_person_id') : null,
            vetterId: $request->filled('vetter_id') ? (int) $request->integer('vetter_id') : null,
            code: (string) $request->string('code'),
            name: (string) $request->string('name'),
            creditHours: (int) $request->integer('credit_hours'),
            isActive: (bool) $request->boolean('is_active', true),
            clos: array_values(array_filter($request->input('clos', []), fn ($row) => !empty($row['statement'] ?? null))),
            requisites: array_values(array_filter($request->input('requisites', []), fn ($row) => !empty($row['course_code'] ?? null))),
            assessments: array_values(array_filter($request->input('assessments', []), fn ($row) => isset($row['component']) && $row['component'] !== '')),
            topics: array_values(array_filter($request->input('topics', []), fn ($row) => !empty($row['title'] ?? null))),
            slt: array_values(array_filter($request->input('slt', []), fn ($row) => !empty($row['activity'] ?? null))),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toMainCourseAttributes(): array
    {
        return [
            'programme_id' => $this->programmeId,
            'lecturer_id' => $this->lecturerId,
            'resource_person_id' => $this->resourcePersonId,
            'vetter_id' => $this->vetterId,
            'code' => $this->code,
            'name' => $this->name,
            'credit_hours' => $this->creditHours,
            'is_active' => $this->isActive,
        ];
    }
}
