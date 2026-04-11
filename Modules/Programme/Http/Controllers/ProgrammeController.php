<?php

namespace Modules\Programme\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Programme\Http\Requests\StoreCLOPLOMappingRequest;
use Modules\Programme\Http\Requests\StoreProgrammePEORequest;
use Modules\Programme\Http\Requests\StoreProgrammePLORequest;
use Modules\Programme\Http\Requests\StoreProgrammeRequest;
use Modules\Programme\Http\Requests\StoreStudyPlanRequest;
use Modules\Programme\Http\Requests\UpdateProgrammeRequest;
use Modules\Programme\Models\CLOPLOMapping;
use Modules\Programme\Models\Programme;
use Modules\Programme\Models\ProgrammePEO;
use Modules\Programme\Models\ProgrammePLO;
use Modules\Programme\Models\StudyPlan;
use Modules\Programme\Models\StudyPlanCourse;
use Modules\Programme\Services\MappingService;
use Modules\Programme\Services\ProgrammeService;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Services\WorkflowService;

class ProgrammeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProgrammeService $programmeService,
        private readonly MappingService $mappingService,
        private readonly WorkflowService $workflowService,
    ) {
    }

    // ==================== Main Programme CRUD ====================

    /**
     * Display list of programmes
     */
    public function index(): View
    {
        $programmes = $this->programmeService->list();

        return view('programme::index', compact('programmes'));
    }

    /**
     * Show create programme form
     */
    public function create(): View
    {
        $chairs = $this->programmeService->getAvailableProgrammeChairs();

        return view('programme::create', compact('chairs'));
    }

    /**
     * Store new programme
     */
    public function store(StoreProgrammeRequest $request): RedirectResponse
    {
        $programme = $this->programmeService->create($request->validated());

        return redirect()->route('programmes.show', $programme)
            ->with('success', 'Programme created successfully.');
    }

    /**
     * Show programme details with tabs
     */
    public function show(Programme $programme): View
    {
        $this->authorize('view', $programme);

        $programme = $this->programmeService->getWithDetails($programme);
        $stats = $this->programmeService->getProgrammeStats($programme);
        $chairs = $this->programmeService->getAvailableProgrammeChairs();
        $workflowInstance = WorkflowInstance::query()
            ->with(['workflow', 'currentStep', 'logs.user', 'logs.workflowStep'])
            ->where('entity_type', Programme::class)
            ->where('entity_id', $programme->id)
            ->latest('id')
            ->first();

        $workflowTimeline = collect();
        $canWorkflowAct = false;

        if ($workflowInstance) {
            $workflowTimeline = $this->workflowService->getWorkflowTimeline($workflowInstance);
            $viewerRoles = auth()->user()?->roles()->pluck('name')->toArray() ?? [];

            $canWorkflowAct = $workflowInstance->isStatus('in_progress')
                && $workflowInstance->currentStep?->userHasRequiredRole($viewerRoles);
        }

        return view('programme::show', compact('programme', 'stats', 'chairs', 'workflowInstance', 'workflowTimeline', 'canWorkflowAct'));
    }

    /**
     * Show edit form
     */
    public function edit(Programme $programme): View
    {
        $this->authorize('update', $programme);

        $chairs = $this->programmeService->getAvailableProgrammeChairs();

        return view('programme::edit', compact('programme', 'chairs'));
    }

    /**
     * Update programme
     */
    public function update(UpdateProgrammeRequest $request, Programme $programme): RedirectResponse
    {
        $this->authorize('update', $programme);

        $this->programmeService->update($programme, $request->validated());

        return redirect()->route('programmes.show', $programme)
            ->with('success', 'Programme updated successfully.');
    }

    /**
     * Delete programme
     */
    public function destroy(Programme $programme): RedirectResponse
    {
        $this->authorize('delete', $programme);

        $this->programmeService->delete($programme);

        return redirect()->route('programmes.index')
            ->with('success', 'Programme deleted successfully.');
    }

    // ==================== API Endpoints ====================

    /**
     * Get list as JSON
     */
    public function listJson(): JsonResponse
    {
        return response()->json($this->programmeService->list());
    }

    // ==================== Programme Learning Outcomes (PLO) ====================

    /**
     * Store new PLO via AJAX
     */
    public function storePLO(StoreProgrammePLORequest $request, Programme $programme): JsonResponse
    {
        $this->authorize('update', $programme);

        $plo = $this->programmeService->createPLO($programme, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'PLO created successfully.',
            'data' => $plo,
        ], 201);
    }

    /**
     * Update PLO via AJAX
     */
    public function updatePLO(StoreProgrammePLORequest $request, ProgrammePLO $plo): JsonResponse
    {
        $this->authorize('update', $plo->programme);

        $plo = $this->programmeService->updatePLO($plo, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'PLO updated successfully.',
            'data' => $plo,
        ]);
    }

    /**
     * Delete PLO via AJAX
     */
    public function deletePLO(ProgrammePLO $plo): JsonResponse
    {
        $this->authorize('update', $plo->programme);

        $this->programmeService->deletePLO($plo);

        return response()->json([
            'success' => true,
            'message' => 'PLO deleted successfully.',
        ]);
    }

    // ==================== Programme Educational Objectives (PEO) ====================

    /**
     * Store new PEO via AJAX
     */
    public function storePEO(StoreProgrammePEORequest $request, Programme $programme): JsonResponse
    {
        $this->authorize('update', $programme);

        $peo = $this->programmeService->createPEO($programme, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'PEO created successfully.',
            'data' => $peo,
        ], 201);
    }

    /**
     * Update PEO via AJAX
     */
    public function updatePEO(StoreProgrammePEORequest $request, ProgrammePEO $peo): JsonResponse
    {
        $this->authorize('update', $peo->programme);

        $peo = $this->programmeService->updatePEO($peo, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'PEO updated successfully.',
            'data' => $peo,
        ]);
    }

    /**
     * Delete PEO via AJAX
     */
    public function deletePEO(ProgrammePEO $peo): JsonResponse
    {
        $this->authorize('update', $peo->programme);

        $this->programmeService->deletePEO($peo);

        return response()->json([
            'success' => true,
            'message' => 'PEO deleted successfully.',
        ]);
    }

    // ==================== Study Plans ====================

    /**
     * Store new study plan
     */
    public function storeStudyPlan(StoreStudyPlanRequest $request, Programme $programme): JsonResponse
    {
        $this->authorize('update', $programme);

        $studyPlan = $this->programmeService->createStudyPlan($programme, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Study plan created successfully.',
            'data' => $studyPlan,
        ], 201);
    }

    /**
     * Update study plan
     */
    public function updateStudyPlan(StoreStudyPlanRequest $request, StudyPlan $studyPlan): JsonResponse
    {
        $this->authorize('update', $studyPlan->programme);

        $studyPlan = $this->programmeService->updateStudyPlan($studyPlan, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Study plan updated successfully.',
            'data' => $studyPlan,
        ]);
    }

    /**
     * Delete study plan
     */
    public function deleteStudyPlan(StudyPlan $studyPlan): JsonResponse
    {
        $this->authorize('update', $studyPlan->programme);

        $this->programmeService->deleteStudyPlan($studyPlan);

        return response()->json([
            'success' => true,
            'message' => 'Study plan deleted successfully.',
        ]);
    }

    /**
     * Get courses by semester for a study plan
     */
    public function getStudyPlanCourses(StudyPlan $studyPlan): JsonResponse
    {
        $courses = $this->programmeService->getStudyPlanCoursesBysemester($studyPlan);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    // ==================== CLO-PLO Mappings ====================

    /**
     * Create or update CLO-PLO mapping
     */
    public function storeMapping(StoreCLOPLOMappingRequest $request): JsonResponse
    {
        $mapping = $this->mappingService->createOrUpdateMapping($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mapping created/updated successfully.',
            'data' => $mapping,
        ], 201);
    }

    /**
     * Get all mappings for a course
     */
    public function getCourseMappings(Programme $programme, int $courseId): JsonResponse
    {
        $mappings = $this->mappingService->getMappingsByCourse(
            $programme->courses()->findOrFail($courseId)
        );

        return response()->json([
            'success' => true,
            'data' => $mappings,
        ]);
    }

    /**
     * Get mapping matrix for a programme
     */
    public function getMappingMatrix(Programme $programme): JsonResponse
    {
        $matrix = $this->mappingService->getMappingMatrix($programme);

        return response()->json([
            'success' => true,
            'data' => $matrix,
        ]);
    }

    /**
     * Delete CLO-PLO mapping
     */
    public function deleteMapping(CLOPLOMapping $mapping): JsonResponse
    {
        $this->authorize('update', $mapping->course->programme);

        $this->mappingService->deleteMapping($mapping);

        return response()->json([
            'success' => true,
            'message' => 'Mapping deleted successfully.',
        ]);
    }

    /**
     * Get CLO coverage report
     */
    public function getCLOCoverageReport(Programme $programme): JsonResponse
    {
        $coverage = $this->mappingService->getCLOCoveragePercentage($programme);
        $mappings = $this->mappingService->getMappingsByProgramme($programme);

        return response()->json([
            'success' => true,
            'data' => [
                'coverage_percentage' => $coverage,
                'total_mappings' => $mappings->count(),
                'mappings' => $mappings,
            ],
        ]);
    }

    // ==================== Programme Chair Assignment ====================

    /**
     * Assign programme chair
     */
    public function assignProgrammeChair(Programme $programme, int $userId): JsonResponse
    {
        $this->authorize('update', $programme);

        $user = \App\Models\User::findOrFail($userId);
        $programme = $this->programmeService->assignProgrammeChair($programme, $user);

        return response()->json([
            'success' => true,
            'message' => 'Programme chair assigned successfully.',
            'data' => $programme->load('programmeChair:id,name,email'),
        ]);
    }

    // ==================== Workflow & Approval ====================

    /**
     * Submit programme for approval
     */
    public function submitForApproval(Programme $programme): JsonResponse
    {
        $this->authorize('update', $programme);

        $programme = $this->programmeService->submitForApproval($programme, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Programme submitted for approval.',
            'data' => $programme,
        ]);
    }
}

