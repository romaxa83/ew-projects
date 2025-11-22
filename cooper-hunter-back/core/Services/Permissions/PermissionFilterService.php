<?php

namespace Core\Services\Permissions;

use App\Contracts\Members\Member;
use App\Contracts\Roles\HasRolesContract;
use Core\Services\Permissions\Filters\PermissionFilter;
use Generator;
use Illuminate\Support\Collection;

class PermissionFilterService
{
    public function filter(HasRolesContract $user, Collection $permissions): Collection
    {
        if (
            !($user instanceof Member)
            || !config('grants.filter_enabled')
        ) {
            return $permissions;
        }

        foreach ($this->getPermissionFilters() as $filter) {
            $permissions = $filter->filter($user, $permissions);

            if ($permissions->isEmpty()) {
                break;
            }
        }

        return $permissions;
    }

    /**
     * @return Generator|array<PermissionFilter>
     */
    protected function getPermissionFilters(): Generator|array
    {
        foreach (array_keys(config('grants.filters')) as $class) {
            yield app($class);
        }
    }
}
