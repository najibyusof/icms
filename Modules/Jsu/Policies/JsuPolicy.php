<?php

namespace Modules\Jsu\Policies;

use App\Models\User;
use Modules\Jsu\Models\Jsu;

class JsuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('jsu.view');
    }

    public function view(User $user, Jsu $jsu): bool
    {
        return $user->can('jsu.view');
    }

    public function create(User $user): bool
    {
        return $user->can('jsu.create');
    }

    public function update(User $user, Jsu $jsu): bool
    {
        return $user->can('jsu.update') && $jsu->isDraft();
    }

    public function delete(User $user, Jsu $jsu): bool
    {
        return $user->can('jsu.update') && $jsu->isDraft();
    }

    public function submit(User $user, Jsu $jsu): bool
    {
        return $user->can('jsu.submit') && $jsu->canSubmit();
    }

    public function approve(User $user, Jsu $jsu): bool
    {
        return $user->can('jsu.approve') && $jsu->isSubmitted();
    }

    public function activate(User $user, Jsu $jsu): bool
    {
        return $user->can('jsu.activate') && $jsu->canActivate();
    }
}
