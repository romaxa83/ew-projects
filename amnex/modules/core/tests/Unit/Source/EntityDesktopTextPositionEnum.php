<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use Wezom\Core\Traits\EnumToArrayTrait;

enum EntityDesktopTextPositionEnum
{
    use EnumToArrayTrait;

    case CENTER;
    case LEFT;
    case RIGHT;
}
