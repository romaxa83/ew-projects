<?php

namespace App\Permissions\Content\OurCases;

use Core\Permissions\BasePermission;

class OurCaseCreatePermission extends BasePermission
{
    public const KEY = 'our_case.create';

    public function getName(): string
    {
        return __('permissions.content.our_case.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
