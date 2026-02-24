<?php

namespace App\Policies;

use App\Models\User;

class NewStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_new_applications');
    }
}
