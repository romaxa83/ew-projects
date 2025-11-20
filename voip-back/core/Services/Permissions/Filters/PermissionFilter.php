<?php

namespace Core\Services\Permissions\Filters;

use App\Models\Employees\Employee;
use App\Models\Permissions\Permission;
use Illuminate\Support\Collection;

interface PermissionFilter
{
    /**
     * @param Employee $user
     * @param Collection<Permission> $permissions
     * @return Collection<Permission>
     */
    public function filter(Employee $user, Collection $permissions): Collection;
}
