<?php

namespace App\Permissions\Catalog\Troubleshoots\Troubleshoot;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.troubleshoot.list';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.troubleshoot.grants.list');
    }

    public function getPosition(): int
    {
        return 64;
    }
}
