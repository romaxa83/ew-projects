<?php

namespace App\Permissions\Catalog\Solutions;

use Core\Permissions\BasePermission;

class SolutionCreateUpdatePermission extends BasePermission
{
    public const KEY = 'catalog.solution.create_update';

    public function getName(): string
    {
        return __('permissions.catalog.solution.grants.create_update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}

