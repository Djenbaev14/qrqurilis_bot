<?php

namespace App\Policies;

use App\Models\User;

class InProgressStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_in_progress_applications');
    }
}
