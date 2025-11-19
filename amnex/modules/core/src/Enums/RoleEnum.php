<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

use Wezom\Core\Traits\EnumToArrayTrait;

enum RoleEnum: string
{
    use EnumToArrayTrait;

    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';

    public function isSuperAdmin(): bool
    {
        return $this === self::SUPER_ADMIN;
    }
}
