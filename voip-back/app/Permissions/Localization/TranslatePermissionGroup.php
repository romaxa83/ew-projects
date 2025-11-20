<?php

namespace App\Permissions\Localization;

use Core\Permissions\BasePermissionGroup;

class TranslatePermissionGroup extends BasePermissionGroup
{
    public const KEY = 'translate';

    public function getName(): string
    {
        return __('permissions.translate.group');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
