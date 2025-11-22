<?php

namespace App\Permissions\About\Pages;

use Core\Permissions\BasePermission;

class PageUpdatePermission extends BasePermission
{
    public const KEY = 'page.update';

    public function getName(): string
    {
        return __('permissions.page.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
