<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

use Wezom\Core\Contracts\OrderColumnEnumInterface;

enum TranslationOrderColumnEnum: string implements OrderColumnEnumInterface
{
    case ID = 'id';
    case SIDE = 'side';
    case LANGUAGE = 'language';
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';
}
