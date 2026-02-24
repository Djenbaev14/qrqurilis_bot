<?php

namespace App\Policies;

use App\Models\User;

class AssignedToCompanyStatusPolicy
{
    // view any
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_assigned_to_company_applications');
    }
}
