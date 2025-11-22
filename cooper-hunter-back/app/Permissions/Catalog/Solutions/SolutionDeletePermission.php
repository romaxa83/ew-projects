<?php

namespace App\Permissions\Catalog\Solutions;

use Core\Permissions\BasePermission;

class SolutionDeletePermission extends BasePermission
{
    public const KEY = 'catalog.solution.delete';

    public function getName(): string
    {
        return __('permissions.catalog.solution.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

