<?php

namespace Modules\Course\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Course\Models\Course;

class CourseService
{
    /**
     * @return Collection<int, Course>
     */
    public function list(): Collection
    {
        return Course::query()
            ->with(['programme'])
            ->orderBy('code')
            ->get();
    }

    public function create(array $payload): Course
    {
        return Course::query()->create($payload);
    }
}
