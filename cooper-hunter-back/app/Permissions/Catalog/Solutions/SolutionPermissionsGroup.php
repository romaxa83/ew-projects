<?php

namespace App\Permissions\Catalog\Solutions;

use Core\Permissions\BasePermissionGroup;

class SolutionPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.solution';

    public function getName(): string
    {
        return __('permissions.catalog.solution.group');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
