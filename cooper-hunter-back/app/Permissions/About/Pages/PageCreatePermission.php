<?php

namespace App\Permissions\About\Pages;

use Core\Permissions\BasePermission;

class PageCreatePermission extends BasePermission
{
    public const KEY = 'page.create';

    public function getName(): string
    {
        return __('permissions.page.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
