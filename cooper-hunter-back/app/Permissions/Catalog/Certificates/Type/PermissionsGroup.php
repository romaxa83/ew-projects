<?php

namespace App\Permissions\Catalog\Certificates\Type;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.certificate.group';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.type.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
