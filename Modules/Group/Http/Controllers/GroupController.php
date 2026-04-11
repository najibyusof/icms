<?php

namespace Modules\Group\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Group\Http\Requests\AssignUsersToGroupRequest;
use Modules\Group\Http\Requests\RemoveUserFromGroupRequest;
use Modules\Group\Http\Requests\StoreGroupRequest;
use Modules\Group\Http\Requests\UpdateGroupCoursesRequest;
use Modules\Group\Http\Requests\UpdateGroupRequest;
use Modules\Group\Models\AcademicGroup;
use Modules\Group\Services\GroupService;

class GroupController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly GroupService $groupService)
    {
    }

    // ==================== View Endpoints ====================

    /**
     * Display list of groups
     */
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', AcademicGroup::class);

        $filters = $request->only(['search', 'programme_id', 'intake_year', 'active']);
        $groups = $this->groupService->filteredList($filters);

        if ($request->expectsJson()) {
            return response()->json($groups);
        }

        $programmeOptions = \Modules\Programme\Models\Programme::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $stats = [
            'total' => $groups->count(),
            'active' => $groups->where('is_active', true)->count(),
            'members' => $groups->sum(fn ($group) => $group->users->count()),
            'courses' => $groups->sum(fn ($group) => $group->courses->count()),
        ];

        return view('group::index', [
            'groups' => $groups,
            'filters' => $filters,
            'programmeOptions' => $programmeOptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Show create group form
     */
    public function create(): View
    {
        $this->authorize('create', AcademicGroup::class);

        $programmes = \Modules\Programme\Models\Programme::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('group::create', compact('programmes'));
    }

    /**
     * Show group details with tabs
     */
    public function show(AcademicGroup $group): View
    {
        $this->authorize('view', $group);

        $group = $this->groupService->getWithDetails($group);
        $stats = $this->groupService->getGroupStats($group);
        $availableCourses = $this->groupService->getAvailableCourses($group);
        $assignedCourses = $group->courses()->get(['courses.id', 'courses.code', 'courses.name', 'courses.credit_hours']);
        $availableUsers = $this->groupService->getAvailableUsers($group);
        $assignedUsers = $this->groupService->getMembers($group);

        return view('group::show', compact('group', 'stats', 'availableCourses', 'assignedCourses', 'availableUsers', 'assignedUsers'));
    }

    /**
     * Show edit form
     */
    public function edit(AcademicGroup $group): View
    {
        $this->authorize('update', $group);

        $programmes = \Modules\Programme\Models\Programme::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('group::edit', compact('group', 'programmes'));
    }

    // ==================== CRUD Operations ====================

    /**
     * Store new group
     */
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $group = $this->groupService->create($request->validated());

        return redirect()->route('groups.show', $group)
            ->with('success', 'Group created successfully.');
    }

    /**
     * Update group
     */
    public function update(UpdateGroupRequest $request, AcademicGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $this->groupService->update($group, $request->validated());

        return redirect()->route('groups.show', $group)
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Delete group
     */
    public function destroy(AcademicGroup $group): RedirectResponse
    {
        $this->authorize('delete', $group);

        $this->groupService->delete($group);

        return redirect()->route('groups.index')
            ->with('success', 'Group deleted successfully.');
    }

    // ==================== API Endpoints ====================

    /**
     * Get list as JSON
     */
    public function listJson(): JsonResponse
    {
        return response()->json($this->groupService->list());
    }

    // ==================== Course Management ====================

    /**
     * Update courses for group
     */
    public function updateCourses(UpdateGroupCoursesRequest $request, AcademicGroup $group): JsonResponse
    {
        $this->authorize('manageCourses', $group);

        $group = $this->groupService->updateCourses($group, $request->validated()['course_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Group courses updated successfully.',
            'data' => $group->courses,
        ]);
    }

    /**
     * Get available courses
     */
    public function getAvailableCourses(AcademicGroup $group): JsonResponse
    {
        $this->authorize('view', $group);

        $courses = $this->groupService->getAvailableCourses($group);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    /**
     * Get assigned courses
     */
    public function getAssignedCourses(AcademicGroup $group): JsonResponse
    {
        $this->authorize('view', $group);

        $courses = $group->courses()->get(['id', 'code', 'name', 'credit_hours']);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    /**
     * Add single course
     */
    public function addCourse(AcademicGroup $group, int $courseId): JsonResponse
    {
        $this->authorize('manageCourses', $group);

        try {
            $this->groupService->addCourse($group, $courseId);

            return response()->json([
                'success' => true,
                'message' => 'Course added successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add course.',
            ], 400);
        }
    }

    /**
     * Remove single course
     */
    public function removeCourse(AcademicGroup $group, int $courseId): JsonResponse
    {
        $this->authorize('manageCourses', $group);

        $this->groupService->removeCourse($group, $courseId);

        return response()->json([
            'success' => true,
            'message' => 'Course removed successfully.',
        ]);
    }

    // ==================== User Management ====================

    /**
     * Assign users to group
     */
    public function assignUsers(AssignUsersToGroupRequest $request, AcademicGroup $group): JsonResponse
    {
        $this->authorize('manageMembers', $group);

        $validated = $request->validated();
        $group = $this->groupService->assignUsers($group, $validated['user_ids'], $validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'Users assigned successfully.',
            'data' => $group->users,
        ]);
    }

    /**
     * Remove user from group
     */
    public function removeUser(RemoveUserFromGroupRequest $request, AcademicGroup $group): JsonResponse
    {
        $this->authorize('manageMembers', $group);

        $userId = $request->validated()['user_id'];
        $group = $this->groupService->removeUser($group, $userId);

        return response()->json([
            'success' => true,
            'message' => 'User removed from group successfully.',
        ]);
    }

    /**
     * Get available users
     */
    public function getAvailableUsers(AcademicGroup $group): JsonResponse
    {
        $this->authorize('view', $group);

        $users = $this->groupService->getAvailableUsers($group);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get group members
     */
    public function getMembers(AcademicGroup $group, ?string $role = null): JsonResponse
    {
        $this->authorize('view', $group);

        $members = $this->groupService->getMembers($group, $role);

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Update user role
     */
    public function updateUserRole(AcademicGroup $group, int $userId, string $role): JsonResponse
    {
        $this->authorize('manageMembers', $group);

        if (!in_array($role, ['member', 'assistant', 'coordinator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role.',
            ], 422);
        }

        $group = $this->groupService->updateUserRole($group, $userId, $role);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully.',
            'data' => $group->users,
        ]);
    }

    // ==================== Statistics & Info ====================

    /**
     * Get group statistics
     */
    public function getStats(AcademicGroup $group): JsonResponse
    {
        $this->authorize('view', $group);

        $stats = $this->groupService->getGroupStats($group);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get affiliated programmes
     */
    public function getProgammes(AcademicGroup $group): JsonResponse
    {
        $this->authorize('view', $group);

        $programmes = $this->groupService->getAffiliatedProgrammes($group);

        return response()->json([
            'success' => true,
            'data' => $programmes,
        ]);
    }
}
