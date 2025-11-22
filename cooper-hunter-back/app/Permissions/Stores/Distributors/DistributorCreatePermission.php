<?php

namespace App\Permissions\Stores\Distributors;

use Core\Permissions\BasePermission;

class DistributorCreatePermission extends BasePermission
{
    public const KEY = DistributorPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.distributor.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
