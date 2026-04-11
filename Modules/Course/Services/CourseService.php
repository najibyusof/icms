<?php

namespace Modules\Course\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Course\DTOs\CourseData;
use Modules\Course\Models\Course;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowSetting;
use Modules\Workflow\Services\WorkflowService;

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

    /**
     * @param array<string, mixed> $filters
     */
    public function paginated(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        $query = Course::query()
            ->with(['programme', 'lecturer', 'resourcePerson', 'vetter'])
            ->orderBy('code');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('programme', fn ($programmeQuery) => $programmeQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($programmeId = $filters['programme_id'] ?? null) {
            $query->where('programme_id', $programmeId);
        }

        if (($filters['active'] ?? null) !== null && $filters['active'] !== '') {
            $query->where('is_active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate($perPage)->withQueryString();
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
            $user = User::query()->findOrFail($initiatedBy);
            $workflowService = app(WorkflowService::class);

            $instance = WorkflowInstance::query()
                ->where('entity_type', Course::class)
                ->where('entity_id', $course->id)
                ->latest('id')
                ->first();

            if (! $instance || in_array($instance->status, ['approved', 'rejected', 'withdrawn'], true)) {
                $instance = $workflowService->startWorkflowForEntityTypeAndVersion(
                    $course,
                    $user,
                    $this->defaultWorkflowTemplateVersion()
                );
            }

            if ($instance->status === 'draft') {
                $workflowService->submit($instance, $user);
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
        $instance = WorkflowInstance::query()
            ->where('entity_type', Course::class)
            ->where('entity_id', $course->id)
            ->with(['workflow.steps', 'currentStep', 'creator', 'logs.user', 'logs.workflowStep'])
            ->latest('id')
            ->first();

        if (! $instance) {
            return [
                'summary' => null,
                'events' => [],
            ];
        }

        $steps = $instance->workflow?->steps ?? collect();
        $approvalCount = $steps->count();
        $completedCount = $instance->status === 'approved'
            ? $approvalCount
            : $instance->logs->where('action', 'approved')->pluck('workflow_step_id')->filter()->unique()->count();

        $events = $instance->logs
            ->sortBy('created_at')
            ->values()
            ->map(function ($log) {
                $status = match ($log->action) {
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                    'clarification_requested' => 'pending',
                    'submitted' => 'submitted',
                    default => 'info',
                };

                return [
                    'label' => $log->workflowStep?->title ?? $log->getActionLabel(),
                    'status' => $status,
                    'by' => $log->user?->name ?? 'System',
                    'at' => $log->created_at,
                    'notes' => $log->comment,
                    'role' => $log->workflowStep && ! empty($log->workflowStep->roles_required)
                        ? collect($log->workflowStep->roles_required)->map(fn($role) => str($role)->headline()->toString())->join(', ')
                        : null,
                    'stage' => $log->workflowStep?->step_number,
                ];
            })
            ->all();

        $pendingStep = $instance->status === 'in_progress' ? $instance->currentStep : null;
        $viewer = $viewerUserId ? User::query()->find($viewerUserId) : null;
        $viewerRoles = $viewer?->roles()->pluck('name')->toArray() ?? [];

        return [
            'summary' => [
                'workflow_id' => $instance->id,
                'status' => $instance->status,
                'current_stage' => $pendingStep?->step_number,
                'approval_count' => $approvalCount,
                'completed_count' => $completedCount,
            ],
            'events' => $events,
            'action' => [
                'workflow_id' => $instance->id,
                'is_actionable' => $pendingStep !== null && $pendingStep->userHasRequiredRole($viewerRoles),
                'pending_stage' => $pendingStep?->step_number,
                'pending_role' => $pendingStep && ! empty($pendingStep->roles_required)
                    ? collect($pendingStep->roles_required)->map(fn($role) => str($role)->headline()->toString())->join(', ')
                    : null,
                'pending_reviewer' => null,
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

    private function defaultWorkflowTemplateVersion(): int
    {
        $dbVersion = WorkflowSetting::get('default_version.course');

        if ($dbVersion !== null && is_numeric($dbVersion) && (int) $dbVersion > 0) {
            return (int) $dbVersion;
        }

        $version = config('workflow.templates.default_versions.course', 1);

        return is_numeric($version) && (int) $version > 0 ? (int) $version : 1;
    }
}
