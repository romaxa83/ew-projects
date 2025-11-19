<?php

declare(strict_types=1);

namespace Wezom\Admins\Enums;

use Wezom\Core\Traits\EnumToArrayTrait;

enum AdminStatusEnum: string
{
    use EnumToArrayTrait;

    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case PENDING = 'PENDING';
}
