<?php

namespace Core\Chat\Enums;

use Core\Enums\BaseEnum;

/**
 * @method static static TEXT()
 * @method static static ATTACHMENT()
 *
 * @method static static SYSTEM()
 * @method static static NOTIFICATION()
 */
class MessageTypeEnum extends BaseEnum
{
    public const TEXT = 'text';
    public const ATTACHMENT = 'attachment';

    public const SYSTEM = 'system';
    public const NOTIFICATION = 'notification';
}
