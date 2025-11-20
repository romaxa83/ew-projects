<?php

namespace App\Enums\Employees;

use Core\Enums\BaseGqlEnum;

/**
 * @method static static PAUSE()     // на паузе, ушел на обед
 * @method static static FREE()    // не в разговоре, может принять звонок
 * @method static static TALK()   // в разговоре, не может принять звонок
 * @method static static ERROR()     // ошибка подключения техники
 */
class Status extends BaseGqlEnum
{
    public const PAUSE = 'pause';
    public const FREE  = 'free';
    public const TALK  = 'talk';
    public const ERROR = 'registration_error';

    public function isPause(): bool
    {
        return $this->is(self::PAUSE());
    }

    public function isFree(): bool
    {
        return $this->is(self::FREE());
    }

    public function isTalk(): bool
    {
        return $this->is(self::TALK());
    }

    public function isError(): bool
    {
        return $this->is(self::ERROR());
    }
}
