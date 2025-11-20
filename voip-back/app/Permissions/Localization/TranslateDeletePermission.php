<?php

namespace App\Permissions\Localization;

use Core\Permissions\BasePermission;

class TranslateDeletePermission extends BasePermission
{
    public const KEY = 'translate.delete';

    public function getName(): string
    {
        return __('permissions.translate.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
