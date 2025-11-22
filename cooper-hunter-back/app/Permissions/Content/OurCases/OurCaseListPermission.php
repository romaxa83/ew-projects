<?php

namespace App\Permissions\Content\OurCases;

use Core\Permissions\BasePermission;

class OurCaseListPermission extends BasePermission
{
    public const KEY = 'our_case.list';

    public function getName(): string
    {
        return __('permissions.content.our_case.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
