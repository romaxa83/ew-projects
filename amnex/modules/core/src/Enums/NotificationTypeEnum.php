<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

use Wezom\Core\Traits\EnumToArrayTrait;

enum NotificationTypeEnum: string
{
    use EnumToArrayTrait;

    case MAIL = 'mail';
    case DATABASE = 'database';
}
