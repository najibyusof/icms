<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Course\Models\Course;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Http\Requests\ApproveWorkflowRequest;
use Modules\Workflow\Http\Requests\ClarificationRequest;
use Modules\Workflow\Http\Requests\CommentWorkflowRequest;
use Modules\Workflow\Http\Requests\RejectWorkflowRequest;
use Modules\Workflow\Http\Requests\SubmitWorkflowRequest;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowSetting;
use Modules\Workflow\Services\WorkflowService;

class WorkflowController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly WorkflowService $service)
    {
    }

    /**
     * List workflow definitions
     */
    public function definitions(Request $request): JsonResponse
    {
        $definitions = $this->service->listDefinitions($request->string('entity_type')->toString() ?: null);

        return response()->json([
            'success' => true,
            'data' => $definitions,
        ]);
    }

    /**
     * Admin screen for workflow definition management
     */
    public function manageDefinitions(Request $request): View
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        $definitions = $this->service->listDefinitions($request->string('entity_type')->toString() ?: null);

        $settings = [
            'course_default_version'    => (int) WorkflowSetting::get('default_version.course',    config('workflow.templates.default_versions.course', 1)),
            'programme_default_version' => (int) WorkflowSetting::get('default_version.programme', config('workflow.templates.default_versions.programme', 1)),
        ];

        return view('workflow::definitions.index', [
            'definitions' => $definitions,
            'entityType'  => $request->string('entity_type')->toString() ?: null,
            'settings'    => $settings,
        ]);
    }

    /**
     * Store workflow definition via admin Blade screen
     */
    public function storeDefinitionWeb(Request $request)
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:workflows,name'],
            'description' => ['nullable', 'string'],
            'entity_type' => ['required', 'in:course,programme'],
            'is_active' => ['sometimes', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.title' => ['required', 'string', 'max:255'],
            'steps.*.description' => ['nullable', 'string'],
            'steps.*.roles_required' => ['required', 'array', 'min:1'],
            'steps.*.roles_required.*' => ['string', 'max:100'],
            'steps.*.approval_level' => ['nullable', 'integer', 'min:1'],
            'steps.*.action_type' => ['nullable', 'in:approve,review,clarification'],
            'steps.*.allow_rejection' => ['nullable', 'boolean'],
            'steps.*.requires_comment' => ['nullable', 'boolean'],
        ]);

        $this->service->createWorkflow($validated);

        return redirect()
            ->route('workflows.manage.definitions')
            ->with('success', 'Workflow definition created successfully.');
    }

    /**
     * Save admin workflow settings (default template versions)
     */
    public function saveSettings(Request $request)
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        $validated = $request->validate([
            'course_default_version'    => ['required', 'integer', 'min:1', 'max:10'],
            'programme_default_version' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        WorkflowSetting::set('default_version.course',    (string) $validated['course_default_version']);
        WorkflowSetting::set('default_version.programme', (string) $validated['programme_default_version']);

        return redirect()
            ->route('workflows.manage.definitions')
            ->with('success', 'Settings saved successfully.');
    }

    /**
     * Create dynamic workflow definition
     */
    public function createDefinition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:workflows,name'],
            'description' => ['nullable', 'string'],
            'entity_type' => ['required', 'in:course,programme'],
            'is_active' => ['sometimes', 'boolean'],
            'config' => ['nullable', 'array'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.title' => ['required', 'string', 'max:255'],
            'steps.*.description' => ['nullable', 'string'],
            'steps.*.roles_required' => ['required', 'array', 'min:1'],
            'steps.*.roles_required.*' => ['string', 'max:100'],
            'steps.*.approval_level' => ['nullable', 'integer', 'min:1'],
            'steps.*.action_type' => ['nullable', 'in:approve,review,clarification'],
            'steps.*.allow_rejection' => ['nullable', 'boolean'],
            'steps.*.requires_comment' => ['nullable', 'boolean'],
        ]);

        $workflow = $this->service->createWorkflow($validated);

        return response()->json([
            'success' => true,
            'message' => 'Workflow definition created successfully.',
            'data' => $workflow,
        ], 201);
    }

    /**
     * Start workflow for Course or Programme entity
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'in:course,programme'],
            'entity_id' => ['required', 'integer'],
            'workflow_name' => ['required', 'string', 'exists:workflows,name'],
        ]);

        $entity = match ($validated['entity_type']) {
            'course' => Course::query()->findOrFail($validated['entity_id']),
            'programme' => Programme::query()->findOrFail($validated['entity_id']),
        };

        $instance = $this->service->startWorkflow($entity, $validated['workflow_name'], auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Workflow instance started successfully.',
            'data' => $instance->load(['workflow', 'currentStep', 'entity']),
        ], 201);
    }

    /**
     * Display workflow timeline view
     */
    public function timeline(WorkflowInstance $workflow): View
    {
        $this->authorize('view', $workflow);

        $timeline = $this->service->getWorkflowTimeline($workflow);
        $workflow->load(['workflow', 'currentStep', 'creator', 'submittedBy', 'approvedBy', 'rejectedBy', 'entity']);

        return view('workflow::timeline', compact('workflow', 'timeline'));
    }

    /**
     * Submit workflow for approval
     */
    public function submit(SubmitWorkflowRequest $request, WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('submit', $workflow);

        try {
            $updated = $this->service->submit($workflow, auth()->user(), $request->input('comment'));

            return response()->json([
                'success' => true,
                'message' => 'Workflow submitted for approval',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve workflow step
     */
    public function approve(ApproveWorkflowRequest $request, WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('decide', $workflow);

        try {
            $updated = $this->service->approve($workflow, auth()->user(), $request->input('comment'));

            return response()->json([
                'success' => true,
                'message' => $updated->isStatus('approved') ? 'Workflow approved' : 'Step approved, moved to next step',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject workflow
     */
    public function reject(RejectWorkflowRequest $request, WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('decide', $workflow);

        try {
            $updated = $this->service->reject($workflow, auth()->user(), $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Workflow rejected',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Request clarification
     */
    public function clarification(ClarificationRequest $request, WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('decide', $workflow);

        try {
            $updated = $this->service->requestClarification($workflow, auth()->user(), $request->input('comment'));

            return response()->json([
                'success' => true,
                'message' => 'Clarification requested',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add comment to workflow
     */
    public function comment(CommentWorkflowRequest $request, WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('comment', $workflow);

        try {
            $this->service->addComment($workflow, auth()->user(), $request->input('comment'));

            return response()->json([
                'success' => true,
                'message' => 'Comment added',
                'data' => $this->service->getWorkflowTimeline($workflow),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Withdraw workflow
     */
    public function withdraw(WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('withdraw', $workflow);

        try {
            $updated = $this->service->withdraw($workflow, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Workflow withdrawn',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get pending approvals for current user
     */
    public function pending(Request $request): JsonResponse
    {
        $entityType = $request->query('entity_type') ?? $request->query('entityType');
        $pending = $this->service->getPendingApprovals(auth()->user(), $entityType);

        return response()->json([
            'success' => true,
            'data' => $pending,
        ]);
    }

    /**
     * Get workflow timeline data
     */
    public function getTimeline(WorkflowInstance $workflow): JsonResponse
    {
        $this->authorize('view', $workflow);

        $timeline = $this->service->getWorkflowTimeline($workflow);

        return response()->json([
            'success' => true,
            'data' => $timeline,
        ]);
    }

    /**
     * Get workflows for an entity
     */
    public function entityWorkflows(string $entityType, int $entityId): JsonResponse
    {
        $model = match ($entityType) {
            'course' => Course::findOrFail($entityId),
            'programme' => Programme::findOrFail($entityId),
            default => throw new \Exception('Unknown entity type'),
        };

        $workflows = $this->service->getEntityWorkflows($model);

        return response()->json([
            'success' => true,
            'data' => $workflows,
        ]);
    }
}
