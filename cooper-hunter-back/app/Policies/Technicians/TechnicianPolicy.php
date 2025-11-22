<?php

namespace App\Policies\Technicians;

use App\Contracts\Roles\HasGuardUser;
use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TechnicianPolicy
{
    use HandlesAuthorization;

    public function before(HasGuardUser $user, $ability): ?bool
    {
        if ($user instanceof Admin || $user instanceof User) {
            return true;
        }

        return null;
    }

    public function isActive(Technician $user): bool
    {
        return $user->isModerated();
    }
}
