<?php

declare(strict_types=1);

namespace Wezom\Quotes\Enums;

use Wezom\Core\Traits\EnumToArrayTrait;

enum QuoteTerminalTypeEnum: string
{
    use EnumToArrayTrait;

    case PICKUP   = 'PICKUP';
    case DELIVERY = 'DELIVERY';
}
