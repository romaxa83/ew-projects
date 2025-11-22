<?php

namespace Core\Services\Permissions\Filters;

use App\Models\Permissions\Permission;
use App\Models\Users\User;
use Illuminate\Support\Collection;

interface PermissionFilter
{
    /**
     * @param User $user
     * @param Collection<Permission> $permissions
     * @return Collection<Permission>
     */
    public function filter(User $user, Collection $permissions): Collection;
}
