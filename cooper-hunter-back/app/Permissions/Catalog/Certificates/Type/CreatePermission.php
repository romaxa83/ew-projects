<?php

namespace App\Permissions\Catalog\Certificates\Type;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.certificate.type.create';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.type.grants.create');
    }

    public function getPosition(): int
    {
        return 61;
    }
}

