<?php

namespace App\Policies;

use App\Models\User;

class RejectedByCompanyStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_rejected_by_company_applications');
    }
}
