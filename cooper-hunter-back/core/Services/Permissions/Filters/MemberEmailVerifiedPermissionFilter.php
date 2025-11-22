<?php

namespace Core\Services\Permissions\Filters;

use App\Contracts\Members\Member;
use Illuminate\Support\Collection;

class MemberEmailVerifiedPermissionFilter extends BasePermissionFilter
{
    public function filter(Member $member, Collection $permissions): Collection
    {
        return $member->isEmailVerified()
            ? $permissions
            : $this->filterPermissions($permissions);
    }
}
