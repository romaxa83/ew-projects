<?php

namespace App\Permissions\Dealers;

use Core\Permissions\BasePermissionGroup;

class DealerPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'dealer';

    public function getName(): string
    {
        return __('permissions.dealer.group');
    }
}
