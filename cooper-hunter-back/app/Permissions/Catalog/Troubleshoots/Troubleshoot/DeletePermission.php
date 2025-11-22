<?php

namespace App\Permissions\Catalog\Troubleshoots\Troubleshoot;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.troubleshoot.delete';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.troubleshoot.grants.delete');
    }

    public function getPosition(): int
    {
        return 63;
    }
}
