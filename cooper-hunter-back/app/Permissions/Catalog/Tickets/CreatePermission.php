<?php

namespace App\Permissions\Catalog\Tickets;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.ticket.create';

    public function getName(): string
    {
        return __('permissions.catalog.ticket.grants.create');
    }

    public function getPosition(): int
    {
        return 41;
    }
}

