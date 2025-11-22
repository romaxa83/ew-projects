<?php

namespace App\Permissions\Catalog\Certificates\Certificate;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.certificate.certificate.list';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.certificate.grants.list');
    }

    public function getPosition(): int
    {
        return 54;
    }
}
