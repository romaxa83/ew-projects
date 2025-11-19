<?php

declare(strict_types=1);

namespace Wezom\Core\Enums\Messages;

enum MessageTypeEnum: string
{
    case SUCCESS = 'SUCCESS';
    case DANGER = 'DANGER';
    case WARNING = 'WARNING';
}
