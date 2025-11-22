<?php

namespace Core\Enums\Messages;

use Core\Enums\BaseEnum;

/**
 * @method static static SUCCESS()
 * @method static static DANGER()
 * @method static static WARNING()
 */
class MessageTypeEnum extends BaseEnum
{
    public const SUCCESS = 'success';
    public const DANGER = 'danger';
    public const WARNING = 'warning';
}
