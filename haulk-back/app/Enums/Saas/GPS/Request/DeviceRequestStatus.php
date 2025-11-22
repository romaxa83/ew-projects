<?php

namespace App\Enums\Saas\GPS\Request;

use App\Enums\BaseEnum;

/**
 * New, In work, Closed
 * @method static static NEW()
 * @method static static IN_WORK()
 * @method static static CLOSED()
 */

// статус запроса на добавление девайсов
class DeviceRequestStatus extends BaseEnum
{
    public const NEW     = 'new';
    public const IN_WORK = 'in_work';
    public const CLOSED  = 'closed';

    public function isNew(): bool
    {
        return $this->is(self::NEW());
    }

    public function isInWork(): bool
    {
        return $this->is(self::IN_WORK());
    }

    public function isClosed(): bool
    {
        return $this->is(self::CLOSED());
    }
}
