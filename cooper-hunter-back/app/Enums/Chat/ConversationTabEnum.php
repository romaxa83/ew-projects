<?php

namespace App\Enums\Chat;

use Core\Enums\BaseEnum;

/**
 * @method static static NEW()
 * @method static static MY()
 * @method static static ALL()
 */
class ConversationTabEnum extends BaseEnum
{
    public const NEW = 'new';
    public const MY = 'my';
    public const ALL = 'all';
}
