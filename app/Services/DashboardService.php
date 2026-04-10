<?php

namespace App\Services;

use App\Support\CanonicalRoleName;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\Course\Models\Course;
use Modules\Examination\Models\Examination;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;

class DashboardService
{
    private const CACHE_KEY = 'dashboard:overview:v2';
    private const CACHE_TTL_MINUTES = 5;

    /**
     * @return array<string, mixed>
     */
    public function getOverview(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function (): array {
            $hasCourses = Schema::hasTable('courses');
            $hasProgrammes = Schema::hasTable('programmes');
            $hasExaminations = Schema::hasTable('examinations');
            $hasWorkflowInstances = Schema::hasTable('workflow_instances');
            $hasWorkflowApprovals = Schema::hasTable('workflow_approvals');

            $totalCourses = $hasCourses ? Course::query()->count() : 0;
            $totalProgrammes = $hasProgrammes ? Programme::query()->count() : 0;

            $draftCount = $hasExaminations ? Examination::query()->where('status', 'draft')->count() : 0;
            $approvedCount = $hasExaminations ? Examination::query()->where('status', 'approved')->count() : 0;

            $workflowStatusCounts = $hasWorkflowInstances
                ? WorkflowInstance::query()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->all()
                : [];

            $recentActivities = $this->recentActivities(
                hasProgrammes: $hasProgrammes,
                hasCourses: $hasCourses,
                hasWorkflowInstances: $hasWorkflowInstances,
            );

            $workflowStageSummary = $hasWorkflowApprovals
                ? WorkflowApproval::query()
                    ->selectRaw('role_name, status, COUNT(*) as total')
                    ->groupBy('role_name', 'status')
                    ->get()
                    ->map(function (WorkflowApproval $approval): object {
                        return (object) [
                            'role_name' => $this->canonicalWorkflowRoleName((string) $approval->role_name),
                            'status' => (string) $approval->status,
                            'total' => (int) $approval->total,
                        ];
                    })
                    ->groupBy(fn (object $row): string => $row->role_name . '|' . $row->status)
                    ->map(function (Collection $rows): object {
                        $first = $rows->first();

                        return (object) [
                            'role_name' => $first->role_name,
                            'status' => $first->status,
                            'total' => $rows->sum('total'),
                        ];
                    })
                    ->sortBy(fn (object $row): string => str_pad((string) CanonicalRoleName::sortOrder($row->role_name), 2, '0', STR_PAD_LEFT) . '-' . $row->status)
                    ->values()
                : collect();

            return [
                'totals' => [
                    'courses' => $totalCourses,
                    'programmes' => $totalProgrammes,
                ],
                'exam_status' => [
                    'draft' => $draftCount,
                    'approved' => $approvedCount,
                ],
                'workflow_status_counts' => $workflowStatusCounts,
                'recent_activities' => $recentActivities,
                'workflow_stage_summary' => $workflowStageSummary,
            ];
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentActivities(bool $hasProgrammes, bool $hasCourses, bool $hasWorkflowInstances): Collection
    {
        $programmeItems = $hasProgrammes
            ? Programme::query()
                ->latest('created_at')
                ->limit(4)
                ->get(['id', 'name', 'created_at'])
                ->map(fn (Programme $p): array => [
                    'type' => 'Programme',
                    'title' => $p->name,
                    'meta' => 'Created programme',
                    'at' => $p->created_at,
                ])
            : collect();

        $courseItems = $hasCourses
            ? Course::query()
                ->latest('created_at')
                ->limit(4)
                ->get(['id', 'name', 'code', 'created_at'])
                ->map(fn (Course $c): array => [
                    'type' => 'Course',
                    'title' => $c->name,
                    'meta' => 'Course ' . $c->code . ' added',
                    'at' => $c->created_at,
                ])
            : collect();

        $workflowItems = $hasWorkflowInstances
            ? WorkflowInstance::query()
                ->latest('updated_at')
                ->limit(4)
                ->get(['id', 'status', 'updated_at'])
                ->map(fn (WorkflowInstance $w): array => [
                    'type' => 'Workflow',
                    'title' => 'Workflow #' . $w->id,
                    'meta' => 'Status: ' . str($w->status)->headline()->toString(),
                    'at' => $w->updated_at,
                ])
            : collect();

        return $programmeItems
            ->concat($courseItems)
            ->concat($workflowItems)
            ->sortByDesc('at')
            ->take(10)
            ->values();
    }

    private function canonicalWorkflowRoleName(string $roleName): string
    {
        return CanonicalRoleName::normalize($roleName);
    }
}
