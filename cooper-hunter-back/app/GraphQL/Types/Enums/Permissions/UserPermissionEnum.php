<?php

declare(strict_types=1);

namespace App\GraphQL\Types\Enums\Permissions;

use App\Models\Users\User;

class UserPermissionEnum extends AdminPermissionEnum
{
    public const NAME = 'UserPermissionEnum';
    public const DESCRIPTION = 'Список всех возможных разрешений для типа: '.self::GUARD;
    public const GUARD = User::GUARD;
}
