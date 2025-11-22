<?php

namespace App\Permissions\Content\OurCases;

use Core\Permissions\BasePermission;

class OurCaseUpdatePermission extends BasePermission
{
    public const KEY = 'our_case.update';

    public function getName(): string
    {
        return __('permissions.content.our_case.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
