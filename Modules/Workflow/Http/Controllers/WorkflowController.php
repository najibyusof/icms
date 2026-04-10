<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Workflow\DTOs\WorkflowDecisionDTO;
use Modules\Workflow\Http\Requests\RecordWorkflowDecisionRequest;
use Modules\Workflow\Services\WorkflowService;

class WorkflowController extends Controller
{
    public function __construct(private readonly WorkflowService $workflowService)
    {
    }

    public function pending(): JsonResponse
    {
        return response()->json($this->workflowService->listPendingForUser((int) auth()->id()));
    }

    public function decide(RecordWorkflowDecisionRequest $request): JsonResponse
    {
        $dto = WorkflowDecisionDTO::fromArray($request->validated(), (int) $request->user()->id);

        return response()->json($this->workflowService->recordDecision($dto));
    }
}
