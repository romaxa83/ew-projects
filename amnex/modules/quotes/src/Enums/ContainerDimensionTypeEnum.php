<?php

declare(strict_types=1);

namespace Wezom\Quotes\Enums;

use Wezom\Core\Traits\Enums\RuleIn;
use Wezom\Core\Traits\EnumToArrayTrait;

enum ContainerDimensionTypeEnum: string
{
    use EnumToArrayTrait;
    use RuleIn;

    case NONE = 'NONE';
    case FT20 = 'FT20';
    case FT40 = 'FT40';
    case FT40_HC = 'FT40_HC';
    case FT53 = 'FT53';
}
