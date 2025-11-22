<?php

namespace App\Permissions\Catalog\Tickets;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.ticket.update';

    public function getName(): string
    {
        return __('permissions.catalog.ticket.grants.update');
    }

    public function getPosition(): int
    {
        return 42;
    }
}

