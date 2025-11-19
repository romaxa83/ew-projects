<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use Wezom\Core\Traits\EnumToArrayTrait;

enum EntityButtonTypeEnum
{
    use EnumToArrayTrait;

    case NONE;
    case BOOKING_WITH_TIMER;
    case CONTACT_CENTER;
}
