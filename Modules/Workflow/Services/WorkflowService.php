<?php

namespace Modules\Workflow\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Course\Models\Course;
use Modules\Jsu\Models\Jsu;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowLog;
use Modules\Workflow\Models\WorkflowStep;

class WorkflowService
{
    /**
     * Start a workflow by entity type and optional template version.
     */
    public function startWorkflowForEntityTypeAndVersion(Model $entity, User $user, ?int $version = null): WorkflowInstance
    {
        $entityType = $this->resolveEntityTypeFromModel($entity);
        $definition = $this->resolveDefinitionByEntityTypeAndVersion($entityType, $version);

        return $this->startWorkflow($entity, $definition->name, $user);
    }

    /**
     * Get workflow definitions by entity type
     */
    public function listDefinitions(?string $entityType = null)
    {
        return Workflow::query()
            ->with('steps')
            ->when($entityType, fn($q) => $q->where('entity_type', $entityType))
            ->orderBy('name')
            ->get();
    }

    /**
     * Start a workflow for an entity
     */
    public function startWorkflow(Model $entity, string $workflowName, User $user): WorkflowInstance
    {
        return DB::transaction(function () use ($entity, $workflowName, $user) {
            $workflow = Workflow::where('name', $workflowName)
                ->where('is_active', true)
                ->firstOrFail();

            $instance = WorkflowInstance::create([
                'workflow_id' => $workflow->id,
                'entity_type' => $entity::class,
                'entity_id' => $entity->id,
                'workflowable_type' => $entity::class,
                'workflowable_id' => $entity->id,
                'status' => 'draft',
                'created_by' => $user->id,
                'initiated_by' => $user->id,
            ]);

            $this->logAction($instance, 'submitted', $user, 'Workflow instance created');

            return $instance;
        });
    }

    /**
     * Submit workflow for approval (moves to first step)
     */
    public function submit(WorkflowInstance $instance, User $user, ?string $comment = null): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $user, $comment) {
            if (!$instance->canEdit()) {
                throw new \Exception('Workflow is not in draft status');
            }

            $firstStep = $instance->workflow->getFirstStep();
            if (!$firstStep) {
                throw new \Exception('Workflow has no configured steps');
            }

            $instance->update([
                'status' => 'in_progress',
                'current_step_id' => $firstStep->id,
                'current_stage' => $firstStep->step_number,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);

            $this->logAction(
                $instance,
                'submitted',
                $user,
                $comment ?? 'Workflow submitted for approval',
                $firstStep
            );

            $this->syncEntityStatus($instance, 'submitted');

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    /**
     * Approve workflow step
     */
    public function approve(WorkflowInstance $instance, User $user, ?string $comment = null): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $user, $comment) {
            if (!$instance->isSubmitted()) {
                throw new \Exception('Workflow is not in progress');
            }

            $currentStep = $instance->currentStep;
            if (!$currentStep) {
                throw new \Exception('Workflow has no active step');
            }

            if ($currentStep->requires_comment && blank($comment)) {
                throw new \Exception('Comment is required for this approval step');
            }

            // Check if user has required role
            $userRoles = $user->roles()->pluck('name')->toArray();
            if (!$currentStep->userHasRequiredRole($userRoles)) {
                throw new \Exception('User does not have required role for approval');
            }

            $nextStep = $instance->workflow->getNextStep($currentStep);

            if ($nextStep) {
                // Move to next step
                $instance->update([
                    'current_step_id' => $nextStep->id,
                    'current_stage' => $nextStep->step_number,
                ]);

                $this->logAction(
                    $instance,
                    'approved',
                    $user,
                    $comment ?? 'Step approved',
                    $currentStep
                );
            } else {
                // Final approval
                $instance->update([
                    'status' => 'approved',
                    'current_step_id' => null,
                    'current_stage' => null,
                    'final_approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                $this->logAction(
                    $instance,
                    'approved',
                    $user,
                    $comment ?? 'Workflow approved',
                    $currentStep
                );

                $this->syncEntityStatus($instance, 'approved');

                // Trigger any approval events/callbacks
                $this->triggerApprovalCallback($instance);
            }

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    /**
     * Reject workflow
     */
    public function reject(WorkflowInstance $instance, User $user, string $reason): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $user, $reason) {
            if (!$instance->isSubmitted()) {
                throw new \Exception('Workflow is not in progress');
            }

            $currentStep = $instance->currentStep;
            if (!$currentStep) {
                throw new \Exception('Workflow has no active step');
            }

            if (!$currentStep->allow_rejection) {
                throw new \Exception('Rejection is not allowed at this step');
            }

            $instance->update([
                'status' => 'rejected',
                'current_step_id' => null,
                'current_stage' => null,
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->logAction(
                $instance,
                'rejected',
                $user,
                $reason,
                $currentStep
            );

            $this->syncEntityStatus($instance, 'rejected');

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    /**
     * Move to next step in workflow
     */
    public function nextStep(WorkflowInstance $instance, User $user): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $user) {
            if (!$instance->isSubmitted()) {
                throw new \Exception('Workflow is not in progress');
            }

            $currentStep = $instance->currentStep;
            if (!$currentStep) {
                throw new \Exception('Workflow has no active step');
            }

            $nextStep = $instance->workflow->getNextStep($currentStep);

            if (!$nextStep) {
                throw new \Exception('No next step available');
            }

            $instance->update([
                'current_step_id' => $nextStep->id,
                'current_stage' => $nextStep->step_number,
            ]);

            $this->logAction(
                $instance,
                'approved',
                $user,
                'Automatic progression to next step',
                $currentStep
            );

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    /**
     * Request clarification on workflow
     */
    public function requestClarification(WorkflowInstance $instance, User $user, string $comment): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $user, $comment) {
            if (!$instance->isSubmitted()) {
                throw new \Exception('Workflow is not in progress');
            }

            $this->logAction(
                $instance,
                'clarification_requested',
                $user,
                $comment,
                $instance->currentStep
            );

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    /**
     * Add comment to workflow
     */
    public function addComment(WorkflowInstance $instance, User $user, string $comment): WorkflowLog
    {
        return $this->logAction(
            $instance,
            'commented',
            $user,
            $comment,
            $instance->currentStep
        );
    }

    /**
     * Withdraw workflow
     */
    public function withdraw(WorkflowInstance $instance, User $user, ?string $reason = null): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $user, $reason) {
            if ($instance->isStatus('approved') || $instance->isStatus('rejected')) {
                throw new \Exception('Cannot withdraw completed workflow');
            }

            $instance->update([
                'status' => 'withdrawn',
                'current_step_id' => null,
                'current_stage' => null,
            ]);

            $this->logAction(
                $instance,
                'withdrawn',
                $user,
                $reason ?? 'Workflow withdrawn by submitter',
                $instance->currentStep
            );

            $this->syncEntityStatus($instance, 'draft');

            return $instance->fresh(['workflow', 'currentStep']);
        });
    }

    /**
     * Get all pending workflows requiring user approval
     */
    public function getPendingApprovals(User $user, string $entityType = null)
    {
        $userRoles = $user->roles()->pluck('name')->toArray();

        $query = WorkflowInstance::where('status', 'in_progress')
            ->with(['workflow', 'currentStep', 'entity', 'creator']);

        if ($entityType) {
            $query->where('entity_type', $this->resolveEntityTypeFilter($entityType));
        }

        return $query->get()
            ->filter(function ($instance) use ($userRoles) {
                return $instance->currentStep?->userHasRequiredRole($userRoles) ?? false;
            });
    }

    private function resolveEntityTypeFilter(string $entityType): string
    {
        return match (strtolower($entityType)) {
            'course', Course::class => Course::class,
            'programme', Programme::class => Programme::class,
            'jsu', Jsu::class => Jsu::class,
            default => $entityType,
        };
    }

    private function resolveEntityTypeFromModel(Model $entity): string
    {
        return match ($entity::class) {
            Course::class => 'course',
            Programme::class => 'programme',
            Jsu::class => 'jsu',
            default => throw new \InvalidArgumentException('Unsupported workflow entity type: ' . $entity::class),
        };
    }

    private function resolveDefinitionByEntityTypeAndVersion(string $entityType, ?int $version): Workflow
    {
        $definitions = Workflow::query()
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->get();

        if ($definitions->isEmpty()) {
            throw new \RuntimeException('No active workflow definition found for entity type: ' . $entityType);
        }

        if ($version !== null) {
            $definitions = $definitions->filter(function (Workflow $workflow) use ($version) {
                return $this->extractVersion($workflow) === $version;
            });

            if ($definitions->isEmpty()) {
                throw new \RuntimeException(
                    sprintf('No active workflow definition found for entity type %s and version %d', $entityType, $version)
                );
            }
        }

        return $definitions
            ->sortByDesc(function (Workflow $workflow) {
                return sprintf('%06d-%010d', $this->extractVersion($workflow), $workflow->id);
            })
            ->first();
    }

    private function extractVersion(Workflow $workflow): int
    {
        $version = data_get($workflow->config, 'version');

        return is_numeric($version) ? (int) $version : 1;
    }

    /**
     * Get workflow history for entity
     */
    public function getEntityWorkflows(Model $entity)
    {
        return WorkflowInstance::where('entity_type', $entity::class)
            ->where('entity_id', $entity->id)
            ->with(['workflow', 'creator', 'submittedBy', 'approvedBy', 'rejectedBy', 'logs.user'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get workflow timeline (logs)
     */
    public function getWorkflowTimeline(WorkflowInstance $instance)
    {
        return $instance->logs()
            ->with(['user', 'workflowStep'])
            ->get();
    }

    /**
     * Log workflow action
     */
    protected function logAction(
        WorkflowInstance $instance,
        string $action,
        User $user,
        ?string $comment = null,
        ?WorkflowStep $step = null
    ): WorkflowLog {
        return WorkflowLog::create([
            'workflow_instance_id' => $instance->id,
            'workflow_step_id' => $step?->id,
            'user_id' => $user->id,
            'action' => $action,
            'comment' => $comment,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Trigger callback when workflow is approved
     */
    protected function triggerApprovalCallback(WorkflowInstance $instance): void
    {
        // Can be extended for custom approval callbacks
        // Example: Update entity status, send notifications, etc.
        // This is a hook point for business logic
    }

    protected function syncEntityStatus(WorkflowInstance $instance, string $status): void
    {
        $entity = $instance->entity;

        if (! $entity || ! isset($entity->status)) {
            return;
        }

        $payload = ['status' => $status];

        if (isset($entity->submitted_at) && $status === 'submitted') {
            $payload['submitted_at'] = now();
        }

        $entity->fill($payload);
        $entity->save();
    }

    /**
     * Create a new workflow definition
     */
    public function createWorkflow(array $data): Workflow
    {
        return DB::transaction(function () use ($data) {
            $workflow = Workflow::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'entity_type' => $data['entity_type'],
                'is_active' => $data['is_active'] ?? true,
                'config' => $data['config'] ?? null,
            ]);

            // Create steps
            if (isset($data['steps'])) {
                foreach ($data['steps'] as $index => $stepData) {
                    WorkflowStep::create([
                        'workflow_id' => $workflow->id,
                        'step_number' => $index + 1,
                        'title' => $stepData['title'],
                        'description' => $stepData['description'] ?? null,
                        'roles_required' => $stepData['roles_required'] ?? [],
                        'approval_level' => $stepData['approval_level'] ?? $index + 1,
                        'action_type' => $stepData['action_type'] ?? 'approve',
                        'allow_rejection' => $stepData['allow_rejection'] ?? true,
                        'requires_comment' => $stepData['requires_comment'] ?? false,
                    ]);
                }
            }

            return $workflow->fresh('steps');
        });
    }
}
