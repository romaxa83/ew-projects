<?php

declare(strict_types=1);

namespace Wezom\Admins\Enums;

use Wezom\Core\Contracts\OrderColumnEnumInterface;

enum AdminOrderColumnEnum: string implements OrderColumnEnumInterface
{
    case ID = 'id';
    case FIRST_NAME = 'first_name';
    case LAST_NAME = 'last_name';
    case FULL_NAME = 'full_name';
    case EMAIL = 'email';
    case POSITION = 'position';
    case STATUS = 'status';
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';
}
