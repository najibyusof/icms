<?php

namespace Modules\Jsu\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Jsu\Models\Jsu;
use Modules\Jsu\Models\JsuBlueprint;
use Modules\Jsu\Models\JsuLog;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowSetting;
use Modules\Workflow\Services\WorkflowService;

class JsuService
{
    public function __construct(private readonly WorkflowService $workflowService)
    {
    }

    // ── Listing ───────────────────────────────────────────────────────────────

    /**
     * @param array{course_id?: int, status?: string, exam_type?: string} $filters
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Jsu::query()
            ->with(['course.programme', 'creator'])
            ->when(
                isset($filters['course_id']),
                fn ($q) => $q->where('course_id', $filters['course_id'])
            )
            ->when(
                isset($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['exam_type']),
                fn ($q) => $q->where('exam_type', $filters['exam_type'])
            )
            ->latest('id');

        return $query->paginate($perPage);
    }

    public function find(int $id): Jsu
    {
        return Jsu::query()
            ->with([
                'course.programme',
                'creator',
                'approver',
                'activator',
                'blueprints.clo',
                'logs.user',
                'workflowInstance.workflow',
                'workflowInstance.currentStep',
            ])
            ->findOrFail($id);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function create(array $data, User $user): Jsu
    {
        return DB::transaction(function () use ($data, $user): Jsu {
            $jsu = Jsu::query()->create([
                'course_id'        => $data['course_id'],
                'created_by'       => $user->id,
                'academic_session' => $data['academic_session'],
                'exam_type'        => $data['exam_type'],
                'title'            => $data['title'],
                'total_marks'      => $data['total_marks'],
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'difficulty_config' => $data['difficulty_config'] ?? null,
                'status'           => 'draft',
            ]);

            $this->log($jsu, $user, 'created', 'JSU created.');

            return $jsu->load(['course.programme', 'creator']);
        });
    }

    public function update(Jsu $jsu, array $data): Jsu
    {
        if (!$jsu->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Only draft JSUs can be updated.'],
            ]);
        }

        $jsu->fill(array_filter([
            'title'            => $data['title'] ?? null,
            'total_marks'      => $data['total_marks'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'difficulty_config' => $data['difficulty_config'] ?? null,
        ], fn ($v) => $v !== null))->save();

        return $jsu->refresh();
    }

    public function delete(Jsu $jsu): void
    {
        if (!$jsu->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Only draft JSUs can be deleted.'],
            ]);
        }

        $jsu->delete();
    }

    // ── Blueprint management ──────────────────────────────────────────────────

    /**
     * Create or update a blueprint row for the given question_no.
     */
    public function upsertBlueprint(Jsu $jsu, array $data): JsuBlueprint
    {
        if (!$jsu->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Blueprint can only be edited on a draft JSU.'],
            ]);
        }

        $blueprint = JsuBlueprint::query()->updateOrCreate(
            ['jsu_id' => $jsu->id, 'question_no' => $data['question_no']],
            [
                'clo_id'            => $data['clo_id'] ?? null,
                'topic'             => $data['topic'] ?? null,
                'bloom_level'       => $data['bloom_level'],
                'marks'             => $data['marks'],
                'weight_percentage' => $data['weight_percentage'] ?? null,
                'notes'             => $data['notes'] ?? null,
            ]
        );

        // Sync totals on the parent JSU
        $this->syncTotals($jsu);

        return $blueprint->load('clo');
    }

    public function deleteBlueprint(Jsu $jsu, JsuBlueprint $blueprint): void
    {
        if (!$jsu->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Blueprint can only be edited on a draft JSU.'],
            ]);
        }

        $blueprint->delete();
        $this->syncTotals($jsu);
    }

    /**
     * Recalculate total_questions and weight_percentage per blueprint row.
     */
    private function syncTotals(Jsu $jsu): void
    {
        $blueprints = $jsu->blueprints()->get();
        $totalMarksBp = $blueprints->sum('marks');

        // Update weight_percentage for each row
        if ($totalMarksBp > 0) {
            foreach ($blueprints as $bp) {
                $bp->weight_percentage = round(($bp->marks / $totalMarksBp) * 100, 2);
                $bp->save();
            }
        }

        $jsu->total_questions = $blueprints->count();
        $jsu->save();
    }

    // ── Difficulty distribution ───────────────────────────────────────────────

    /**
     * Returns per-group actual vs. target distribution for the JSU.
     *
     * @return array<string, array{bloom_levels: int[], target_pct: float, actual_pct: float, marks: float, within_tolerance: bool}>
     */
    public function checkDifficultyDistribution(Jsu $jsu): array
    {
        $blueprints  = $jsu->blueprints()->get();
        $totalMarks  = $blueprints->sum('marks');
        $config      = $jsu->effectiveDifficultyConfig();
        $tolerance   = (int) config('jsu.distribution_tolerance', 5);
        $result      = [];

        foreach ($config as $group => $definition) {
            $levels    = $definition['bloom_levels'] ?? [];
            $targetPct = (float) ($definition['target_pct'] ?? 0);
            $groupMarks = $blueprints->whereIn('bloom_level', $levels)->sum('marks');
            $actualPct = $totalMarks > 0 ? round(($groupMarks / $totalMarks) * 100, 2) : 0.0;

            $result[$group] = [
                'bloom_levels'      => $levels,
                'target_pct'        => $targetPct,
                'actual_pct'        => $actualPct,
                'marks'             => (float) $groupMarks,
                'within_tolerance'  => abs($actualPct - $targetPct) <= $tolerance,
            ];
        }

        return $result;
    }

    // ── Workflow ──────────────────────────────────────────────────────────────

    public function submitForApproval(Jsu $jsu, User $user): WorkflowInstance
    {
        if (!$jsu->canSubmit()) {
            throw ValidationException::withMessages([
                'status' => ['JSU must be in draft or rejected status to submit.'],
            ]);
        }

        if ($jsu->blueprints()->count() === 0) {
            throw ValidationException::withMessages([
                'blueprints' => ['JSU must have at least one blueprint entry before submission.'],
            ]);
        }

        return DB::transaction(function () use ($jsu, $user): WorkflowInstance {
            $version = $this->defaultWorkflowVersion();

            $instance = $this->workflowService->startWorkflowForEntityTypeAndVersion($jsu, $user, $version);
            $this->workflowService->submit($instance, $user);

            $this->log($jsu, $user, 'submitted', 'Submitted for approval.');

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    public function approve(Jsu $jsu, User $user, ?string $comment = null): WorkflowInstance
    {
        $instance = $this->activeWorkflowInstance($jsu);

        $result = $this->workflowService->approve($instance, $user, $comment);

        if ($jsu->fresh()->isApproved()) {
            $jsu->update(['approved_by' => $user->id, 'approved_at' => now()]);
            $this->log($jsu, $user, 'approved', $comment ?? 'JSU approved.');
        }

        return $result;
    }

    public function reject(Jsu $jsu, User $user, string $reason): WorkflowInstance
    {
        $instance = $this->activeWorkflowInstance($jsu);

        $result = $this->workflowService->reject($instance, $user, $reason);

        $this->log($jsu, $user, 'rejected', $reason);

        return $result;
    }

    /**
     * Activate an approved JSU — marks it as the current active JSU for the course/session.
     */
    public function activate(Jsu $jsu, User $user): Jsu
    {
        if (!$jsu->canActivate()) {
            throw ValidationException::withMessages([
                'status' => ['Only approved JSUs can be activated.'],
            ]);
        }

        DB::transaction(function () use ($jsu, $user): void {
            // Deactivate any previously active JSU for same course + session + type
            Jsu::query()
                ->where('course_id', $jsu->course_id)
                ->where('academic_session', $jsu->academic_session)
                ->where('exam_type', $jsu->exam_type)
                ->where('status', 'active')
                ->where('id', '!=', $jsu->id)
                ->update(['status' => 'approved']);

            $jsu->update([
                'status'       => 'active',
                'activated_by' => $user->id,
                'activated_at' => now(),
            ]);

            $this->log($jsu, $user, 'activated', 'JSU activated.');
        });

        return $jsu->refresh();
    }

    // ── Logging ───────────────────────────────────────────────────────────────

    public function log(
        Jsu $jsu,
        User $user,
        string $action,
        ?string $comment = null,
        ?array $metadata = null,
    ): JsuLog {
        return JsuLog::query()->create([
            'jsu_id'     => $jsu->id,
            'user_id'    => $user->id,
            'action'     => $action,
            'comment'    => $comment,
            'metadata'   => $metadata,
            'created_at' => now(),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function activeWorkflowInstance(Jsu $jsu): WorkflowInstance
    {
        $instance = WorkflowInstance::query()
            ->where('entity_type', Jsu::class)
            ->where('entity_id', $jsu->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        if (!$instance) {
            throw ValidationException::withMessages([
                'workflow' => ['No active workflow found for this JSU.'],
            ]);
        }

        return $instance;
    }

    private function defaultWorkflowVersion(): int
    {
        $dbVersion = WorkflowSetting::get('default_version.jsu');

        if ($dbVersion !== null && is_numeric($dbVersion) && (int) $dbVersion > 0) {
            return (int) $dbVersion;
        }

        return (int) config('jsu.workflow_default_version', 1);
    }
}
