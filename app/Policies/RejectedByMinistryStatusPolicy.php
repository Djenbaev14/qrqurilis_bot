<?php

namespace App\Policies;

use App\Models\User;

class RejectedByMinistryStatusPolicy
{
    // view any
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_rejected_by_ministry_applications');
    }
}
