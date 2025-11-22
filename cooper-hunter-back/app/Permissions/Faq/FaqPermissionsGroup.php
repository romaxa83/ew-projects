<?php

namespace App\Permissions\Faq;

use Core\Permissions\BasePermissionGroup;

class FaqPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'faq';

    public function getName(): string
    {
        return __('permissions.faq.group');
    }

    public function getPosition(): int
    {
        return 66;
    }
}
