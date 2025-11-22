<?php

namespace App\Permissions\Catalog\Certificates\Certificate;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.certificate.certificate.create';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.certificate.grants.create');
    }

    public function getPosition(): int
    {
        return 51;
    }
}

