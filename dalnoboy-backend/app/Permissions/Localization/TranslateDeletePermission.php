<?php

namespace App\Permissions\Localization;

use App\Enums\Permissions\AdminRolesEnum;
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

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
