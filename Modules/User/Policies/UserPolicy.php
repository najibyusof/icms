<?php

namespace Modules\User\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Admins can delete any user except themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->isAdmin($user) && $user->id !== $model->id;
    }
}
