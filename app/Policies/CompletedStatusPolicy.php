<?php

namespace App\Policies;

use App\Models\User;

class CompletedStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_completed_applications');
    }
}
