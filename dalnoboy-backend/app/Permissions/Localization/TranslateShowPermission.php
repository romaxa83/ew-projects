<?php

namespace App\Permissions\Localization;

use Core\Permissions\BasePermission;

class TranslateShowPermission extends BasePermission
{
    public const KEY = 'translate.show';

    public function getName(): string
    {
        return __('permissions.translate.grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return true;
    }
}
