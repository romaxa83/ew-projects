<?php

namespace Core\Services\Permissions\Filters;

use App\Models\Users\User;
use Illuminate\Support\Collection;

class UserEmailVerifiedPermissionFilter extends BasePermissionFilter
{
    public function filter(User $user, Collection $permissions): Collection
    {
        return $user->isEmailVerified()
            ? $permissions
            : $this->filterPermissions($permissions);
    }
}
