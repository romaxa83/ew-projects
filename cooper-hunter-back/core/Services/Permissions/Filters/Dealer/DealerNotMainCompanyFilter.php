<?php

namespace Core\Services\Permissions\Filters\Dealer;

use App\Contracts\Members\Member;
use App\Models\Dealers\Dealer;
use App\Models\Permissions\Permission;
use Core\Services\Permissions\Filters\BasePermissionFilter;
use Illuminate\Support\Collection;

class DealerNotMainCompanyFilter extends BasePermissionFilter
{
    public function filter(Member $member, Collection $permissions): Collection
    {
        if (!$member instanceof Dealer) {
            return $permissions;
        }

        if (!$member->isMainCompany()) {
            return $this->filterPermissions($permissions);
        }

        return $permissions;
    }

    protected function filterPermissions(Collection $permissions): Collection
    {
        $removePermissions = array_map(
            static fn(string $permissionClass) => app($permissionClass)->getKey(),
            $this->getAllowedPermissions()
        );

        $removePermissionsFlipped = array_flip($removePermissions);

        return $permissions->filter(
            fn(Permission $permission) => !array_key_exists($permission->name, $removePermissionsFlipped)
        );
    }
}


