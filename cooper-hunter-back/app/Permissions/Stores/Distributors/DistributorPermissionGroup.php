<?php

namespace App\Permissions\Stores\Distributors;

use Core\Permissions\BasePermissionGroup;

class DistributorPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'distributor';

    public function getName(): string
    {
        return __('permissions.distributor.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
