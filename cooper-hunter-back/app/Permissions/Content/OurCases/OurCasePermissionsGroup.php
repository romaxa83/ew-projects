<?php

namespace App\Permissions\Content\OurCases;

use Core\Permissions\BasePermissionGroup;

class OurCasePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'our_case';

    public function getName(): string
    {
        return __('permissions.content.our_case.group');
    }

    public function getPosition(): int
    {
        return 75;
    }
}
