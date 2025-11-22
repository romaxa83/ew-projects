<?php

namespace Core\Services\Permissions\Filters;

use App\Contracts\Members\Member;
use App\Models\Permissions\Permission;
use Illuminate\Support\Collection;

interface PermissionFilter
{
    /**
     * @param  Member  $member
     * @param  Collection<Permission>  $permissions
     * @return Collection<Permission>
     */
    public function filter(Member $member, Collection $permissions): Collection;
}
