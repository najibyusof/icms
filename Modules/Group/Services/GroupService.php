<?php

namespace Modules\Group\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Group\Models\AcademicGroup;

class GroupService
{
    /**
     * @return Collection<int, AcademicGroup>
     */
    public function list(): Collection
    {
        return AcademicGroup::query()
            ->with(['programme', 'coordinator', 'courses'])
            ->orderByDesc('intake_year')
            ->orderBy('name')
            ->get();
    }

    public function create(array $payload): AcademicGroup
    {
        return DB::transaction(function () use ($payload): AcademicGroup {
            $group = AcademicGroup::query()->create(Arr::except($payload, ['course_ids']));

            if (! empty($payload['course_ids'])) {
                $group->courses()->sync($payload['course_ids']);
            }

            return $group->load(['programme', 'coordinator', 'courses']);
        });
    }
}
