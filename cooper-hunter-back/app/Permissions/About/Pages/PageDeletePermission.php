<?php

namespace App\Permissions\About\Pages;

use Core\Permissions\BasePermission;

class PageDeletePermission extends BasePermission
{
    public const KEY = 'page.delete';

    public function getName(): string
    {
        return __('permissions.page.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
