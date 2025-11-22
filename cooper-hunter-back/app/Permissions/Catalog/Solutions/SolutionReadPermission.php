<?php

namespace App\Permissions\Catalog\Solutions;

use Core\Permissions\BasePermission;

class SolutionReadPermission extends BasePermission
{
    public const KEY = 'catalog.solution.read';

    public function getName(): string
    {
        return __('permissions.catalog.solution.grants.read');
    }

    public function getPosition(): int
    {
        return 2;
    }
}

