<?php

namespace App\Permissions\Catalog\Certificates\Type;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.certificate.type.delete';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.type.grants.delete');
    }

    public function getPosition(): int
    {
        return 63;
    }
}
