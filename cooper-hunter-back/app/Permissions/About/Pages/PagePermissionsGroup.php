<?php

namespace App\Permissions\About\Pages;

use Core\Permissions\BasePermissionGroup;

class PagePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'page';

    public function getName(): string
    {
        return __('permissions.page.group');
    }

    public function getPosition(): int
    {
        return 75;
    }
}
