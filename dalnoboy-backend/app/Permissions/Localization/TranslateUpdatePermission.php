<?php

namespace App\Permissions\Localization;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class TranslateUpdatePermission extends BasePermission
{
    public const KEY = 'translate.update';

    public function getName(): string
    {
        return __('permissions.translate.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
