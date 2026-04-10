<?php

namespace Modules\Course\Policies;

use App\Models\User;
use Modules\Course\Models\Course;

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

    public function update(User $user, Course $course): bool
    {
        return $user->can('course.update');
    }

    public function submit(User $user, Course $course): bool
    {
        return $user->can('course.submit');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('course.update');
    }
}
