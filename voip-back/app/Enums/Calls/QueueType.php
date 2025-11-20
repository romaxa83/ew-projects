<?php

namespace App\Enums\Calls;

use Core\Enums\BaseGqlEnum;

/**
 * @method static static QUEUE()    // звонок из очереди
 * @method static static DIAL()     // звонок между агентами
 */
class QueueType extends BaseGqlEnum
{
    public const QUEUE = 'queue';
    public const DIAL  = 'dial';

    public function isQueue(): bool
    {
        return $this->is(self::QUEUE());
    }

    public function isDial(): bool
    {
        return $this->is(self::DIAL());
    }
}
