<?php

namespace App\Permissions\Stores\Distributors;

use Core\Permissions\BasePermission;

class DistributorDeletePermission extends BasePermission
{
    public const KEY = DistributorPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.distributor.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
