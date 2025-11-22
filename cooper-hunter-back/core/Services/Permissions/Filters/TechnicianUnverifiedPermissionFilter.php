<?php

namespace Core\Services\Permissions\Filters;

use App\Contracts\Members\Member;
use App\Models\Permissions\Permission;
use App\Models\Technicians\Technician;
use Illuminate\Support\Collection;

class TechnicianUnverifiedPermissionFilter extends BasePermissionFilter
{
    public function filter(Member $member, Collection $permissions): Collection
    {
        if (!$member instanceof Technician) {
            return $permissions;
        }

        if (!$member->is_verified) {
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
