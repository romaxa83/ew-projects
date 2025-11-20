<?php

namespace App\Enums\Calls;

use Core\Enums\BaseGqlEnum;

/**
 * @method static static WAIT()         // звонок появился в очереди, но на него еще не ответили
 * @method static static CONNECTION()   // есть конект с агентом, но разговор еще не начался
 * @method static static TALK()         // начался разговор с агентом
 * @method static static CANCEL()       // звонок завершен
 */
class QueueStatus extends BaseGqlEnum
{
    public const WAIT       = 'wait';
    public const CONNECTION = 'connection';
    public const TALK       = 'talk';
    public const CANCEL     = 'cancel';

    public function isCancel(): bool
    {
        return $this->is(self::CANCEL());
    }

    public function isWait(): bool
    {
        return $this->is(self::WAIT());
    }

    public function isConnection(): bool
    {
        return $this->is(self::CONNECTION());
    }

    public function isTalk(): bool
    {
        return $this->is(self::TALK());
    }

    public static function forApi(): array
    {
        return [
            self::TALK,
            self::CONNECTION,
            self::WAIT,
        ];
    }
}
