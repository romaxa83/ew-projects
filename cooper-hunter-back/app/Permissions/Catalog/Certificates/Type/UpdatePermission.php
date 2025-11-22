<?php

namespace App\Permissions\Catalog\Certificates\Type;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.certificate.type.update';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.type.grants.update');
    }

    public function getPosition(): int
    {
        return 62;
    }
}

