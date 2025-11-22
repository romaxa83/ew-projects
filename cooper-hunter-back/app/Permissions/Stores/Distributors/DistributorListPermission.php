<?php

namespace App\Permissions\Stores\Distributors;

use Core\Permissions\BasePermission;

class DistributorListPermission extends BasePermission
{
    public const KEY = DistributorPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.distributor.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
