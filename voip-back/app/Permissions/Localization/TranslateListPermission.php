<?php

namespace App\Permissions\Localization;

use Core\Permissions\BasePermission;

class TranslateListPermission extends BasePermission
{
    public const KEY = 'translate.list';

    public function getName(): string
    {
        return __('permissions.translate.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
