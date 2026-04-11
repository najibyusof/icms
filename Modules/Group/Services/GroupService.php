<?php

namespace Modules\Group\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Group\Models\AcademicGroup;

class GroupService
{
    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, AcademicGroup>
     */
    public function filteredList(array $filters = []): Collection
    {
        $query = AcademicGroup::query()
            ->with(['programme', 'coordinator', 'courses', 'users'])
            ->orderByDesc('intake_year')
            ->orderBy('semester')
            ->orderBy('name');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhereHas('programme', fn (Builder $programmeQuery) => $programmeQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('coordinator', fn (Builder $coordinatorQuery) => $coordinatorQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($programmeId = $filters['programme_id'] ?? null) {
            $query->where('programme_id', $programmeId);
        }

        if ($intakeYear = $filters['intake_year'] ?? null) {
            $query->where('intake_year', $intakeYear);
        }

        if (($filters['active'] ?? null) !== null && $filters['active'] !== '') {
            $query->where('is_active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->get();
    }

    /**
     * @return Collection<int, AcademicGroup>
     */
    public function list(): Collection
    {
        return $this->filteredList();
    }

    /**
     * Get detailed group with all relationships
     */
    public function getWithDetails(AcademicGroup $group): AcademicGroup
    {
        return AcademicGroup::with([
            'programme:id,code,name',
            'coordinator:id,name,email',
            'courses:id,code,name,credit_hours',
            'users' => fn ($q) => $q
                ->select('users.id', 'users.name', 'users.email', 'users.staff_id')
                ->withPivot(['role']),
        ])->findOrFail($group->id);
    }

    public function create(array $payload): AcademicGroup
    {
        return DB::transaction(function () use ($payload): AcademicGroup {
            $courseIds = Arr::pull($payload, 'course_ids', []);
            $userIds = Arr::pull($payload, 'user_ids', []);

            $group = AcademicGroup::query()->create($payload);

            if (!empty($courseIds)) {
                $group->courses()->sync($courseIds);
            }

            if (!empty($userIds)) {
                // Assign users with 'member' role by default
                $userRoles = array_fill_keys($userIds, ['role' => 'member']);
                $group->users()->sync($userRoles);
            }

            return $group->load(['programme', 'coordinator', 'courses', 'users']);
        });
    }

    public function update(AcademicGroup $group, array $payload): AcademicGroup
    {
        return DB::transaction(function () use ($group, $payload): AcademicGroup {
            $group->update($payload);
            return $group->load(['programme', 'coordinator', 'courses', 'users']);
        });
    }

    public function delete(AcademicGroup $group): bool
    {
        return $group->delete();
    }

    /**
     * Update courses for a group
     */
    public function updateCourses(AcademicGroup $group, array $courseIds): AcademicGroup
    {
        $group->courses()->sync($courseIds);
        return $group->load(['courses']);
    }

    /**
     * Add a course to the group
     */
    public function addCourse(AcademicGroup $group, int $courseId): AcademicGroup
    {
        $group->courses()->attach($courseId);
        return $group;
    }

    /**
     * Remove a course from the group
     */
    public function removeCourse(AcademicGroup $group, int $courseId): AcademicGroup
    {
        $group->courses()->detach($courseId);
        return $group;
    }

    /**
     * Assign users to a group
     */
    public function assignUsers(AcademicGroup $group, array $userIds, string $role = 'member'): AcademicGroup
    {
        return DB::transaction(function () use ($group, $userIds, $role): AcademicGroup {
            // Prepare user roles array
            $userRoles = array_fill_keys($userIds, ['role' => $role]);
            $group->users()->syncWithoutDetaching($userRoles);
            return $group->load(['users']);
        });
    }

    /**
     * Remove a user from the group
     */
    public function removeUser(AcademicGroup $group, int $userId): AcademicGroup
    {
        $group->users()->detach($userId);
        return $group->load(['users']);
    }

    /**
     * Update user role in group
     */
    public function updateUserRole(AcademicGroup $group, int $userId, string $role): AcademicGroup
    {
        $group->users()->updateExistingPivot($userId, ['role' => $role]);
        return $group->load(['users']);
    }

    /**
     * Get group members
     */
    public function getMembers(AcademicGroup $group, ?string $role = null)
    {
        $query = $group->users();

        if ($role) {
            $query->wherePivot('role', $role);
        }

        return $query->get();
    }

    /**
     * Get available courses not in group
     */
    public function getAvailableCourses(AcademicGroup $group)
    {
        $assignedCourseIds = $group->courses()->pluck('courses.id')->toArray();

        return \Modules\Course\Models\Course::where('programme_id', $group->programme_id)
            ->where('is_active', true)
            ->whereNotIn('id', $assignedCourseIds)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'credit_hours']);
    }

    /**
     * Get available users (not already assigned to group)
     */
    public function getAvailableUsers(AcademicGroup $group)
    {
        $assignedUserIds = $group->users()->pluck('users.id')->toArray();

        return User::whereNotIn('id', $assignedUserIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'staff_id']);
    }

    /**
     * Get affiliated programmes
     */
    public function getAffiliatedProgrammes(AcademicGroup $group)
    {
        // A group is directly affiliated with one programme
        return collect([$group->programme]);
    }

    /**
     * Get group statistics
     */
    public function getGroupStats(AcademicGroup $group): array
    {
        return [
            'total_members' => $group->users()->count(),
            'total_courses' => $group->courses()->count(),
            'members_by_role' => [
                'member' => $group->users()->wherePivot('role', 'member')->count(),
                'assistant' => $group->users()->wherePivot('role', 'assistant')->count(),
                'coordinator' => $group->users()->wherePivot('role', 'coordinator')->count(),
            ],
            'programme' => $group->programme?->name,
        ];
    }
}
