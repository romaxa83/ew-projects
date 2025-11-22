<?php

namespace App\Permissions\Catalog\Certificates\Certificate;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.certificate.certificate.update';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.certificate.grants.update');
    }

    public function getPosition(): int
    {
        return 42;
    }
}

