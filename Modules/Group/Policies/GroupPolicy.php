<?php

namespace Modules\Group\Policies;

use App\Models\User;
use Modules\Group\Models\AcademicGroup;

class GroupPolicy
{
    /**
     * Determine whether the user can view any groups
     */
    public function viewAny(User $user): bool
    {
        return $user->can('group.view');
    }

    /**
     * Determine whether the user can view the group
     */
    public function view(User $user, AcademicGroup $group): bool
    {
        return $user->can('group.view');
    }

    /**
     * Determine whether the user can create groups
     */
    public function create(User $user): bool
    {
        return $user->can('group.create');
    }

    /**
     * Determine whether the user can update the group
     * Only assigned members or group coordinator can edit
     */
    public function update(User $user, AcademicGroup $group): bool
    {
        // Check if user has global edit permission
        if ($user->can('group.edit')) {
            return true;
        }

        // Check if user is group coordinator
        if ($group->coordinator_id === $user->id) {
            return true;
        }

        // Check if user is assigned to the group
        return $group->hasMember($user);
    }

    /**
     * Determine whether the user can delete the group
     */
    public function delete(User $user, AcademicGroup $group): bool
    {
        // Only admins or group coordinator can delete
        if ($user->can('group.delete')) {
            return true;
        }

        return $group->coordinator_id === $user->id;
    }

    /**
     * Determine whether the user can manage group members
     */
    public function manageMembers(User $user, AcademicGroup $group): bool
    {
        if ($user->can('group.edit')) {
            return true;
        }

        return $group->coordinator_id === $user->id;
    }

    /**
     * Determine whether the user can manage group courses
     */
    public function manageCourses(User $user, AcademicGroup $group): bool
    {
        if ($user->can('group.edit')) {
            return true;
        }

        return $group->coordinator_id === $user->id;
    }
}
