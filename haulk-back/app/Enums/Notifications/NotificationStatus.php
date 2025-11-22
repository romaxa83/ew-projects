<?php

namespace App\Enums\Notifications;

use App\Enums\BaseEnum;

/**
 * @method static static NEW()
 * @method static static READ()
 */

class NotificationStatus extends BaseEnum
{
    public const NEW  = 'new';
    public const READ = 'read';

    public function isNew(): bool
    {
        return $this->is(self::NEW());
    }

    public function isRead(): bool
    {
        return $this->is(self::READ());
    }
}

