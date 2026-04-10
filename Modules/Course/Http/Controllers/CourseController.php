<?php

namespace Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Course\DTOs\CourseData;
use Modules\Course\Exports\CourseSltExport;
use Modules\Course\Http\Requests\UpsertCourseRequest;
use Modules\Course\Models\Course;
use Modules\Course\Http\Requests\StoreCourseRequest;
use Modules\Course\Services\CourseService;
use Modules\Programme\Models\Programme;
use Modules\Workflow\DTOs\WorkflowDecisionDTO;
use Modules\Workflow\Http\Requests\RecordWorkflowDecisionRequest;
use Modules\Workflow\Services\WorkflowService;

class CourseController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function index(Request $request): JsonResponse|View
    {
        if (! $request->expectsJson()) {
            $this->authorize('viewAny', Course::class);

            return view('courses.index', [
                'courses' => $this->courseService->paginated(),
            ]);
        }

        return response()->json($this->courseService->list());
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        return response()->json(
            $this->courseService->create($request->validated()),
            201
        );
    }

    public function create(): View
    {
        $this->authorize('create', Course::class);

        return view('courses.form', [
            'course' => new Course(['is_active' => true, 'status' => 'draft']),
            'programmes' => Programme::query()->orderBy('name')->get(),
            'lecturers' => User::query()->role('Lecturer')->orWhereHas('roles', fn ($q) => $q->where('name', 'lecturer'))->orderBy('name')->get(),
            'resourcePeople' => User::query()->orderBy('name')->get(),
            'vetters' => User::query()->role('Reviewer')->orWhereHas('roles', fn ($q) => $q->where('name', 'reviewer'))->orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function storeWeb(UpsertCourseRequest $request): RedirectResponse
    {
        $course = $this->courseService->createWithDetails(CourseData::fromRequest($request));

        return redirect()->route('courses.edit', $course)->with('success', 'Course created successfully.');
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['clos', 'requisites', 'assessments', 'topics', 'sltItems']);

        return view('courses.form', [
            'course' => $course,
            'programmes' => Programme::query()->orderBy('name')->get(),
            'lecturers' => User::query()->role('Lecturer')->orWhereHas('roles', fn ($q) => $q->where('name', 'lecturer'))->orderBy('name')->get(),
            'resourcePeople' => User::query()->orderBy('name')->get(),
            'vetters' => User::query()->role('Reviewer')->orWhereHas('roles', fn ($q) => $q->where('name', 'reviewer'))->orderBy('name')->get(),
            'workflowTimeline' => $this->courseService->workflowTimeline($course, (int) auth()->id()),
            'mode' => 'edit',
        ]);
    }

    public function update(UpsertCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $this->courseService->updateWithDetails($course, CourseData::fromRequest($request));

        return redirect()->route('courses.edit', $course)->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $this->courseService->delete($course);

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }

    public function submit(Course $course): RedirectResponse
    {
        $this->authorize('submit', $course);

        $this->courseService->submitForApproval($course, (int) auth()->id());

        return redirect()->route('courses.edit', $course)->with('success', 'Course submitted for approval.');
    }

    public function exportSlt(Course $course)
    {
        $this->authorize('update', $course);

        $rows = $this->courseService->sltExportRows($course);

        return Excel::download(new CourseSltExport($rows), 'course-slt-' . $course->code . '.xlsx');
    }

    public function decideWorkflow(
        RecordWorkflowDecisionRequest $request,
        Course $course,
        WorkflowService $workflowService,
    ): RedirectResponse {
        $workflowId = (int) $request->integer('workflow_id');

        abort_unless(
            $course->status !== 'draft' && $this->courseService->workflowTimeline($course, (int) $request->user()->id)['action']['workflow_id'] === $workflowId,
            404
        );

        $workflowService->recordDecision(WorkflowDecisionDTO::fromArray($request->validated(), (int) $request->user()->id));

        return redirect()->route('courses.edit', $course)->with('success', 'Workflow decision recorded successfully.');
    }
}
