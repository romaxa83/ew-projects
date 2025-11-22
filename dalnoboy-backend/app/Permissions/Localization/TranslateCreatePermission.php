<?php

namespace App\Permissions\Localization;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class TranslateCreatePermission extends BasePermission
{
    public const KEY = TranslatePermissionGroup::KEY . '.create';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions.' . TranslatePermissionGroup::KEY . '.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
