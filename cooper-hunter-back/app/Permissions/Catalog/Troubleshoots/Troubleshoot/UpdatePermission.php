<?php

namespace App\Permissions\Catalog\Troubleshoots\Troubleshoot;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.troubleshoot.update';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.troubleshoot.grants.update');
    }

    public function getPosition(): int
    {
        return 62;
    }
}

