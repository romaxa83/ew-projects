<?php

namespace App\Permissions\Content\OurCases;

use Core\Permissions\BasePermission;

class OurCaseDeletePermission extends BasePermission
{
    public const KEY = 'our_case.delete';

    public function getName(): string
    {
        return __('permissions.content.our_case.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
