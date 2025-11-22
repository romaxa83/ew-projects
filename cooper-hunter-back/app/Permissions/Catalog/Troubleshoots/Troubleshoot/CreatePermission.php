<?php

namespace App\Permissions\Catalog\Troubleshoots\Troubleshoot;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.troubleshoot.create';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.troubleshoot.grants.create');
    }

    public function getPosition(): int
    {
        return 61;
    }
}
