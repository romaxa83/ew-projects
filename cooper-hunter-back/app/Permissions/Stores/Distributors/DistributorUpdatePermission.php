<?php

namespace App\Permissions\Stores\Distributors;

use Core\Permissions\BasePermission;

class DistributorUpdatePermission extends BasePermission
{
    public const KEY = DistributorPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.distributor.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
