<?php

namespace Modules\Jsu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Course\Models\Course;
use Modules\Jsu\Http\Requests\StoreBlueprintRequest;
use Modules\Jsu\Http\Requests\StoreJsuRequest;
use Modules\Jsu\Http\Requests\UpdateJsuRequest;
use Modules\Jsu\Models\Jsu;
use Modules\Jsu\Models\JsuBlueprint;
use Modules\Jsu\Services\JsuService;

class JsuController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly JsuService $service)
    {
    }

    // ── Web management screens ───────────────────────────────────────────────

    public function manageIndex(Request $request): View
    {
        $this->authorize('viewAny', Jsu::class);

        return view('jsu.index', [
            'jsuList' => $this->service->list(
                $request->only(['course_id', 'status', 'exam_type']),
                (int) $request->input('per_page', 20),
            ),
            'filters' => $request->only(['course_id', 'status', 'exam_type']),
            'courses' => Course::query()->orderBy('code')->get(['id', 'code', 'name']),
            'examTypes' => config('jsu.exam_types', []),
        ]);
    }

    public function manageCreate(): View
    {
        $this->authorize('create', Jsu::class);

        return view('jsu.create', [
            'courses' => Course::query()->orderBy('code')->get(['id', 'code', 'name']),
            'examTypes' => config('jsu.exam_types', []),
            'difficultyConfig' => config('jsu.difficulty_distribution', []),
        ]);
    }

    public function manageStore(StoreJsuRequest $request): RedirectResponse
    {
        $jsu = $this->service->create($request->validated(), $request->user());

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'JSU created successfully. Add blueprint entries and submit for approval.');
    }

    public function manageShow(Jsu $jsu): View
    {
        $this->authorize('view', $jsu);

        $jsu = $this->service->find($jsu->id);

        return view('jsu.show', [
            'jsu' => $jsu,
            'distribution' => $this->service->checkDifficultyDistribution($jsu),
            'tolerance' => config('jsu.distribution_tolerance', 5),
            'bloomLevels' => config('jsu.bloom_levels', []),
            'clos' => $jsu->course->clos()->orderBy('clo_no')->get(),
        ]);
    }

    public function manageStoreBlueprint(StoreBlueprintRequest $request, Jsu $jsu): RedirectResponse
    {
        $this->authorize('update', $jsu);

        $this->service->upsertBlueprint($jsu, $request->validated());

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'Blueprint row saved.');
    }

    public function manageDestroyBlueprint(Jsu $jsu, JsuBlueprint $blueprint): RedirectResponse
    {
        $this->authorize('update', $jsu);
        abort_if($blueprint->jsu_id !== $jsu->id, 404);

        $this->service->deleteBlueprint($jsu, $blueprint);

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'Blueprint row deleted.');
    }

    public function manageSubmit(Request $request, Jsu $jsu): RedirectResponse
    {
        $this->authorize('submit', $jsu);

        $this->service->submitForApproval($jsu, $request->user());

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'JSU submitted for approval.');
    }

    public function manageApprove(Request $request, Jsu $jsu): RedirectResponse
    {
        $this->authorize('approve', $jsu);
        $validated = $request->validate(['comment' => ['nullable', 'string']]);

        $this->service->approve($jsu, $request->user(), $validated['comment'] ?? null);

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'JSU workflow step approved.');
    }

    public function manageReject(Request $request, Jsu $jsu): RedirectResponse
    {
        $this->authorize('approve', $jsu);
        $validated = $request->validate(['reason' => ['required', 'string']]);

        $this->service->reject($jsu, $request->user(), $validated['reason']);

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'JSU rejected.');
    }

    public function manageActivate(Request $request, Jsu $jsu): RedirectResponse
    {
        $this->authorize('activate', $jsu);

        $this->service->activate($jsu, $request->user());

        return redirect()
            ->route('jsu.manage.show', $jsu)
            ->with('success', 'JSU activated.');
    }

    // ── JSU CRUD ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Jsu::class);

        $jsuList = $this->service->list(
            $request->only(['course_id', 'status', 'exam_type']),
            (int) $request->input('per_page', 20),
        );

        return response()->json($jsuList);
    }

    public function store(StoreJsuRequest $request): JsonResponse
    {
        $jsu = $this->service->create($request->validated(), $request->user());

        return response()->json($jsu, 201);
    }

    public function show(int $id): JsonResponse
    {
        $jsu = $this->service->find($id);
        $this->authorize('view', $jsu);

        return response()->json($jsu);
    }

    public function update(UpdateJsuRequest $request, Jsu $jsu): JsonResponse
    {
        $this->authorize('update', $jsu);

        $jsu = $this->service->update($jsu, $request->validated());

        return response()->json($jsu);
    }

    public function destroy(Jsu $jsu): JsonResponse
    {
        $this->authorize('delete', $jsu);

        $this->service->delete($jsu);

        return response()->json(['message' => 'JSU deleted.']);
    }

    // ── Workflow actions ──────────────────────────────────────────────────────

    public function submit(Request $request, Jsu $jsu): JsonResponse
    {
        $this->authorize('submit', $jsu);

        $instance = $this->service->submitForApproval($jsu, $request->user());

        return response()->json([
            'message'  => 'JSU submitted for approval.',
            'workflow' => $instance,
        ]);
    }

    public function approve(Request $request, Jsu $jsu): JsonResponse
    {
        $this->authorize('approve', $jsu);

        $validated = $request->validate([
            'comment' => ['nullable', 'string'],
        ]);

        $instance = $this->service->approve($jsu, $request->user(), $validated['comment'] ?? null);

        return response()->json([
            'message'  => 'Step approved.',
            'workflow' => $instance,
        ]);
    }

    public function reject(Request $request, Jsu $jsu): JsonResponse
    {
        $this->authorize('approve', $jsu);

        $validated = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $instance = $this->service->reject($jsu, $request->user(), $validated['reason']);

        return response()->json([
            'message'  => 'JSU rejected.',
            'workflow' => $instance,
        ]);
    }

    public function activate(Request $request, Jsu $jsu): JsonResponse
    {
        $this->authorize('activate', $jsu);

        $jsu = $this->service->activate($jsu, $request->user());

        return response()->json([
            'message' => 'JSU activated.',
            'jsu'     => $jsu,
        ]);
    }

    // ── Blueprint ─────────────────────────────────────────────────────────────

    public function blueprints(Jsu $jsu): JsonResponse
    {
        $this->authorize('view', $jsu);

        return response()->json($jsu->blueprints()->with('clo')->get());
    }

    public function storeBlueprint(StoreBlueprintRequest $request, Jsu $jsu): JsonResponse
    {
        $this->authorize('update', $jsu);

        $blueprint = $this->service->upsertBlueprint($jsu, $request->validated());

        return response()->json($blueprint, 201);
    }

    public function destroyBlueprint(Jsu $jsu, JsuBlueprint $blueprint): JsonResponse
    {
        $this->authorize('update', $jsu);

        abort_if($blueprint->jsu_id !== $jsu->id, 404);

        $this->service->deleteBlueprint($jsu, $blueprint);

        return response()->json(['message' => 'Blueprint entry removed.']);
    }

    // ── Distribution ──────────────────────────────────────────────────────────

    public function distribution(Jsu $jsu): JsonResponse
    {
        $this->authorize('view', $jsu);

        $distribution = $this->service->checkDifficultyDistribution($jsu);

        $allWithin = collect($distribution)->every(fn ($g) => $g['within_tolerance']);

        return response()->json([
            'distribution'  => $distribution,
            'is_balanced'   => $allWithin,
            'tolerance_pct' => config('jsu.distribution_tolerance', 5),
        ]);
    }

    // ── Logs ─────────────────────────────────────────────────────────────────

    public function logs(Jsu $jsu): JsonResponse
    {
        $this->authorize('view', $jsu);

        return response()->json($jsu->logs()->with('user')->get());
    }
}
