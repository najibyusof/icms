<?php

namespace Modules\Course\Policies;

use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('course.view');
    }

    public function create(User $user): bool
    {
        return $user->can('course.create');
    }
}
