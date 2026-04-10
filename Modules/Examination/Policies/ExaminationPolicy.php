<?php

namespace Modules\Examination\Policies;

use App\Models\User;
use Modules\Examination\Models\Examination;

class ExaminationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('examination.view');
    }

    public function create(User $user): bool
    {
        return $user->can('examination.submit');
    }

    public function view(User $user, Examination $examination): bool
    {
        return $user->can('examination.view') || $examination->submitted_by === $user->id;
    }
}
