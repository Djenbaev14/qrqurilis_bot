<?php

namespace App\Policies;

use App\Models\User;

class ConfirmedByCitizenStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_confirmed_by_citizen_applications');
    }
}
