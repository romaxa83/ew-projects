<?php

namespace App\Permissions\Catalog\Certificates\Type;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.certificate.type.list';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.type.grants.list');
    }

    public function getPosition(): int
    {
        return 64;
    }
}
