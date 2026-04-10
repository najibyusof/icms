<?php

namespace Modules\Course\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Course\DTOs\CourseData;
use Modules\Course\Models\Course;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;

class CourseService
{
    /**
     * @return Collection<int, Course>
     */
    public function list(): Collection
    {
        return Course::query()
            ->with(['programme'])
            ->orderBy('code')
            ->get();
    }

    public function paginated(int $perPage = 12): LengthAwarePaginator
    {
        return Course::query()
            ->with(['programme', 'lecturer', 'resourcePerson', 'vetter'])
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function create(array $payload): Course
    {
        return Course::query()->create($payload);
    }

    public function createWithDetails(CourseData $data): Course
    {
        $this->assertAssessmentTotal($data->assessments);

        return DB::transaction(function () use ($data): Course {
            $course = Course::query()->create($data->toMainCourseAttributes() + ['status' => 'draft']);

            $this->syncDetailTables($course, $data);

            return $this->loadCourseGraph($course);
        });
    }

    public function updateWithDetails(Course $course, CourseData $data): Course
    {
        $this->assertAssessmentTotal($data->assessments);

        return DB::transaction(function () use ($course, $data): Course {
            $course->update($data->toMainCourseAttributes());

            $this->syncDetailTables($course, $data);

            return $this->loadCourseGraph($course);
        });
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    public function submitForApproval(Course $course, int $initiatedBy): void
    {
        DB::transaction(function () use ($course, $initiatedBy): void {
            $course->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            if (Schema::hasTable('workflow_instances')) {
                $workflow = WorkflowInstance::query()->firstOrCreate(
                    [
                        'workflowable_type' => Course::class,
                        'workflowable_id' => $course->id,
                    ],
                    [
                        'initiated_by' => $initiatedBy,
                        'status' => 'in_review',
                        'current_stage' => 1,
                    ]
                );

                if (Schema::hasTable('workflow_approvals')) {
                    $reviewerId = $course->vetter_id ?: User::role(['Reviewer', 'reviewer'])->value('id');
                    $approverId = User::role(['Approver', 'approver'])->value('id');

                    if ($reviewerId) {
                        WorkflowApproval::query()->firstOrCreate(
                            [
                                'workflow_instance_id' => $workflow->id,
                                'stage' => 1,
                            ],
                            [
                                'reviewer_id' => $reviewerId,
                                'role_name' => 'reviewer',
                                'status' => 'pending',
                            ]
                        );
                    }

                    if ($approverId) {
                        WorkflowApproval::query()->firstOrCreate(
                            [
                                'workflow_instance_id' => $workflow->id,
                                'stage' => 2,
                            ],
                            [
                                'reviewer_id' => $approverId,
                                'role_name' => 'approver',
                                'status' => 'queued',
                            ]
                        );
                    }
                }
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sltExportRows(Course $course): array
    {
        $course->loadMissing('sltItems');

        $rows = [];
        foreach ($course->sltItems as $item) {
            $rows[] = [
                'activity' => $item->activity,
                'f2f_hours' => $item->f2f_hours,
                'non_f2f_hours' => $item->non_f2f_hours,
                'independent_hours' => $item->independent_hours,
                'total_hours' => $item->total_hours,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function workflowTimeline(Course $course, ?int $viewerUserId = null): array
    {
        if (! Schema::hasTable('workflow_instances')) {
            return [
                'summary' => null,
                'events' => [],
            ];
        }

        $instance = WorkflowInstance::query()
            ->where('workflowable_type', Course::class)
            ->where('workflowable_id', $course->id)
            ->with(['initiator'])
            ->latest('created_at')
            ->first();

        if (! $instance) {
            return [
                'summary' => null,
                'events' => [],
            ];
        }

        $events = [[
            'label' => 'Submitted',
            'status' => $instance->status,
            'by' => $instance->initiator?->name ?? 'System',
            'at' => $instance->created_at,
            'notes' => 'Current stage: ' . ($instance->current_stage ?? '-'),
            'stage' => 0,
        ]];

        $approvalCount = 0;
        $completedCount = 0;

        if (Schema::hasTable('workflow_approvals')) {
            $approvals = WorkflowApproval::query()
                ->where('workflow_instance_id', $instance->id)
                ->with('reviewer')
                ->orderBy('stage')
                ->orderBy('created_at')
                ->get();

            $approvalCount = $approvals->count();
            $completedCount = $approvals->whereIn('status', ['approved', 'rejected'])->count();

            foreach ($approvals as $approval) {
                $events[] = [
                    'label' => 'Stage ' . $approval->stage,
                    'status' => $approval->status,
                    'by' => $approval->reviewer?->name ?? 'Pending assignment',
                    'at' => $approval->acted_at ?? $approval->created_at,
                    'notes' => $approval->comments,
                    'role' => str($approval->role_name)->headline()->toString(),
                    'stage' => $approval->stage,
                ];
            }
        }

        $pendingApproval = null;
        if (Schema::hasTable('workflow_approvals')) {
            $pendingApproval = WorkflowApproval::query()
                ->where('workflow_instance_id', $instance->id)
                ->where('status', 'pending')
                ->orderBy('stage')
                ->with('reviewer')
                ->first();
        }

        return [
            'summary' => [
                'status' => $instance->status,
                'current_stage' => $instance->current_stage,
                'approval_count' => $approvalCount,
                'completed_count' => $completedCount,
            ],
            'events' => $events,
            'action' => [
                'workflow_id' => $instance->id,
                'is_actionable' => $pendingApproval !== null
                    && $viewerUserId !== null
                    && (int) $pendingApproval->reviewer_id === $viewerUserId,
                'pending_stage' => $pendingApproval?->stage,
                'pending_role' => $pendingApproval?->role_name,
                'pending_reviewer' => $pendingApproval?->reviewer?->name,
            ],
        ];
    }

    private function loadCourseGraph(Course $course): Course
    {
        return $course->fresh([
            'programme',
            'lecturer',
            'resourcePerson',
            'vetter',
            'clos',
            'requisites',
            'assessments',
            'topics',
            'sltItems',
        ]);
    }

    private function syncDetailTables(Course $course, CourseData $data): void
    {
        $course->clos()->delete();
        $course->requisites()->delete();
        $course->assessments()->delete();
        $course->topics()->delete();
        $course->sltItems()->delete();

        foreach (array_values($data->clos) as $i => $row) {
            $course->clos()->create([
                'clo_no' => $i + 1,
                'statement' => (string) ($row['statement'] ?? ''),
                'bloom_level' => (string) ($row['bloom_level'] ?? 'C1'),
            ]);
        }

        foreach ($data->requisites as $row) {
            $course->requisites()->create([
                'type' => (string) ($row['type'] ?? 'prerequisite'),
                'course_code' => (string) ($row['course_code'] ?? ''),
                'course_name' => (string) ($row['course_name'] ?? ''),
            ]);
        }

        foreach ($data->assessments as $row) {
            $course->assessments()->create([
                'component' => (string) ($row['component'] ?? ''),
                'weightage' => (float) ($row['weightage'] ?? 0),
                'remarks' => (string) ($row['remarks'] ?? ''),
            ]);
        }

        foreach ($data->topics as $row) {
            $course->topics()->create([
                'week_no' => (int) ($row['week_no'] ?? 1),
                'title' => (string) ($row['title'] ?? ''),
                'learning_activity' => (string) ($row['learning_activity'] ?? ''),
            ]);
        }

        foreach ($data->slt as $row) {
            $f2f = (float) ($row['f2f_hours'] ?? 0);
            $nonF2f = (float) ($row['non_f2f_hours'] ?? 0);
            $independent = (float) ($row['independent_hours'] ?? 0);

            $course->sltItems()->create([
                'activity' => (string) ($row['activity'] ?? ''),
                'f2f_hours' => $f2f,
                'non_f2f_hours' => $nonF2f,
                'independent_hours' => $independent,
                'total_hours' => $f2f + $nonF2f + $independent,
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $assessments
     */
    private function assertAssessmentTotal(array $assessments): void
    {
        $sum = 0.0;
        foreach ($assessments as $row) {
            $sum += (float) ($row['weightage'] ?? 0);
        }

        if (round($sum, 2) !== 100.0) {
            throw ValidationException::withMessages([
                'assessments' => 'Assessment total weightage must be exactly 100%.',
            ]);
        }
    }
}
