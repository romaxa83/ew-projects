<?php

namespace App\Permissions\Catalog\Certificates\Certificate;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.certificate.certificate';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.certificate.group');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
