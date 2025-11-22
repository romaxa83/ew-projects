<?php

declare(strict_types=1);

namespace App\GraphQL\Types\Enums\Permissions;

use App\Models\Users\User;

class EmployeePermissionEnum extends AdminPermissionEnum
{
    public const NAME = 'EmployeePermissionEnum';
    public const DESCRIPTION = 'Список всех возможных разрешений для типа: ' . self::GUARD;
    public const GUARD = User::GUARD;
}
