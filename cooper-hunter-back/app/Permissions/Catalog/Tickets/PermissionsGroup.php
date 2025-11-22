<?php

namespace App\Permissions\Catalog\Tickets;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.ticket';

    public function getName(): string
    {
        return __('permissions.catalog.ticket.group');
    }

    public function getPosition(): int
    {
        return 41;
    }
}
