<?php

namespace Modules\Programme\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Programme\Models\Programme;

class ProgrammeService
{
    /**
     * @return Collection<int, Programme>
     */
    public function list(): Collection
    {
        return Programme::query()
            ->withCount(['courses', 'groups'])
            ->orderBy('name')
            ->get();
    }

    public function create(array $payload): Programme
    {
        return Programme::query()->create($payload);
    }
}
