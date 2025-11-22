<?php

namespace App\Permissions\Catalog\Certificates\Certificate;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.certificate.certificate.delete';

    public function getName(): string
    {
        return __('permissions.catalog.certificate.certificate.grants.delete');
    }

    public function getPosition(): int
    {
        return 53;
    }
}
